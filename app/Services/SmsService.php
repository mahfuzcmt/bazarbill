<?php

namespace App\Services;

use App\Exceptions\InsufficientSmsCreditsException;
use App\Models\Market;
use App\Models\Setting;
use App\Models\SmsCreditTransaction;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public const GATEWAY_PLATFORM = 'platform';
    public const GATEWAY_OWN = 'own';

    /** Error message from the last failed send, if any. */
    public ?string $lastError = null;

    /** True when the last failure was caused by an empty credit balance. */
    public bool $lastFailedForCredits = false;

    public function __construct(
        protected ?Market $market = null,
        protected ?SmsCreditService $credits = null,
    ) {
        $this->credits ??= new SmsCreditService();
    }

    public function setMarket(Market $market): self
    {
        $this->market = $market;
        return $this;
    }

    /**
     * Which gateway this market sends through.
     */
    public function gateway(): string
    {
        return $this->market?->usesPlatformSms() ? self::GATEWAY_PLATFORM : self::GATEWAY_OWN;
    }

    public function usesPlatformGateway(): bool
    {
        return $this->gateway() === self::GATEWAY_PLATFORM;
    }

    /**
     * Whether an API key exists for the gateway this market uses.
     */
    public function isConfigured(): bool
    {
        return $this->market !== null && $this->apiKey() !== null;
    }

    protected function apiKey(): ?string
    {
        return $this->usesPlatformGateway()
            ? Setting::smsApiKey()
            : $this->market->sms_api_key;
    }

    protected function senderId(): string
    {
        if ($this->usesPlatformGateway()) {
            // Branded sender IDs on the platform gateway are a paid plan feature.
            if ($this->market->sms_sender_id && $this->market->planAllows('masking_sms')) {
                return $this->market->sms_sender_id;
            }

            return Setting::smsSenderId();
        }

        return $this->market->sms_sender_id ?: Setting::smsSenderId();
    }

    /**
     * Number of SMS segments the gateway will bill for this message.
     * Unicode (Bangla) messages fit 70 chars in one part, 67 per part after that.
     * Plain GSM text fits 160 chars in one part, 153 per part after that.
     */
    public static function calculateSegments(string $message, bool $isUnicode = true): int
    {
        $length = mb_strlen($message);

        if ($length === 0) {
            return 1;
        }

        [$single, $multi] = $isUnicode ? [70, 67] : [160, 153];

        return $length <= $single ? 1 : (int) ceil($length / $multi);
    }

    public function send(string $phone, string $message, bool $isUnicode = true): bool
    {
        if (!$this->market) {
            throw new \RuntimeException('Market not set for SMS service');
        }

        $this->lastError = null;
        $this->lastFailedForCredits = false;

        if (!$this->isConfigured()) {
            $this->lastError = $this->usesPlatformGateway()
                ? 'Platform SMS gateway not configured (super admin: Admin > SMS Gateway)'
                : 'Market SMS API key not set';
            Log::warning('SMS gateway not configured for market', [
                'market_id' => $this->market->id,
                'gateway' => $this->gateway(),
            ]);
            SmsLog::create([
                'market_id' => $this->market->id,
                'recipient_phone' => $this->normalizePhone($phone),
                'message' => $message,
                'status' => 'failed',
                'gateway' => $this->gateway(),
                'credits_used' => 0,
                'api_response' => $this->lastError,
            ]);
            return false;
        }

        $phone = $this->normalizePhone($phone);
        $platform = $this->usesPlatformGateway();
        $segments = self::calculateSegments($message, $isUnicode);

        $smsLog = SmsLog::create([
            'market_id' => $this->market->id,
            'recipient_phone' => $phone,
            'message' => $message,
            'status' => 'pending',
            'gateway' => $this->gateway(),
            'credits_used' => $platform ? $segments : 0,
        ]);

        // Reserve credits before talking to the gateway so concurrent sends
        // can never overdraw the balance. Failed sends are refunded below.
        if ($platform) {
            try {
                $this->credits->debit($this->market, $segments, SmsCreditTransaction::TYPE_USAGE, [
                    'sms_log_id' => $smsLog->id,
                    'note' => 'SMS to ' . $phone,
                ]);
            } catch (InsufficientSmsCreditsException $e) {
                $smsLog->update(['credits_used' => 0]);
                $smsLog->markAsFailed($e->getMessage());
                $this->lastError = $e->getMessage();
                $this->lastFailedForCredits = true;
                Log::warning('SMS blocked: insufficient credits', [
                    'market_id' => $this->market->id,
                    'required' => $segments,
                    'available' => $e->available,
                ]);
                return false;
            }
        }

        try {
            $response = Http::timeout(30)->get(config('services.sms.url', 'http://bulksmsbd.net/api/smsapi'), [
                'api_key' => $this->apiKey(),
                'senderid' => $this->senderId(),
                'number' => $phone,
                'message' => $message,
                'type' => $isUnicode ? 'unicode' : 'text',
            ]);

            $responseBody = $response->body();

            if ($response->successful()) {
                // bulksmsbd.net returns JSON with response_code 202 on success
                $responseData = json_decode($responseBody, true);

                if (isset($responseData['response_code']) && $responseData['response_code'] == 202) {
                    $smsLog->markAsSent($responseBody);
                    return true;
                }

                $this->lastError = $responseData['error_message'] ?? $responseBody;
                Log::error('SMS send failed', [
                    'market_id' => $this->market->id,
                    'phone' => $phone,
                    'response' => $responseBody,
                ]);
            } else {
                $this->lastError = 'HTTP ' . $response->status();
            }

            $this->fail($smsLog, $responseBody, $platform, $segments);
            return false;

        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error('SMS send exception', [
                'market_id' => $this->market->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            $this->fail($smsLog, $e->getMessage(), $platform, $segments);
            return false;
        }
    }

    protected function fail(SmsLog $smsLog, ?string $response, bool $platform, int $segments): void
    {
        $smsLog->markAsFailed($response);

        if ($platform) {
            $smsLog->update(['credits_used' => 0]);
            $this->credits->refund($this->market, $segments, [
                'sms_log_id' => $smsLog->id,
                'note' => 'Refund for failed SMS',
            ]);
        }
    }

    public function sendInvoiceNotification(array $data): bool
    {
        $template = $this->market->getSmsTemplate('invoice_generated')
            ?? 'প্রিয় {shop_owner}, আপনার {month} মাসের ভাড়া {amount} টাকা। বিল নং: {invoice_no}';

        $message = $this->parseTemplate($template, $data);

        return $this->send($data['phone'], $message);
    }

    public function sendPaymentReminder(array $data): bool
    {
        $template = $this->market->getSmsTemplate('payment_reminder')
            ?? 'প্রিয় {shop_owner}, আপনার {amount} টাকা বকেয়া আছে। অনুগ্রহ করে পরিশোধ করুন।';

        $message = $this->parseTemplate($template, $data);

        return $this->send($data['phone'], $message);
    }

    public function sendPaymentConfirmation(array $data): bool
    {
        $template = $this->market->getSmsTemplate('payment_received')
            ?? 'ধন্যবাদ! {amount} টাকা পেমেন্ট গৃহীত হয়েছে। রসিদ নং: {receipt_no}';

        $message = $this->parseTemplate($template, $data);

        return $this->send($data['phone'], $message);
    }

    protected function parseTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Convert any common Bangladeshi mobile format to the gateway's 8801XXXXXXXXX form.
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Already international: 8801XXXXXXXXX
        if (str_starts_with($phone, '880')) {
            return $phone;
        }

        // Local format 01XXXXXXXXX keeps its leading zero after the country code.
        if (str_starts_with($phone, '0')) {
            return '88' . $phone;
        }

        // Bare subscriber number 1XXXXXXXXX
        return '880' . $phone;
    }

    /**
     * Send one SMS with the platform credentials, outside any market:
     * no log, no credits. Used by the super admin "test gateway" button.
     *
     * @return array{ok:bool, error:?string, response:?string}
     */
    public static function platformTest(string $phone, string $message): array
    {
        $service = new static();
        $phone = $service->normalizePhone($phone);

        try {
            $response = Http::timeout(30)->get(config('services.sms.url', 'http://bulksmsbd.net/api/smsapi'), [
                'api_key' => Setting::smsApiKey(),
                'senderid' => Setting::smsSenderId(),
                'number' => $phone,
                'message' => $message,
                'type' => 'text',
            ]);

            $body = $response->body();
            $data = json_decode($body, true);

            if ($response->successful() && isset($data['response_code']) && $data['response_code'] == 202) {
                return ['ok' => true, 'error' => null, 'response' => $body];
            }

            return [
                'ok' => false,
                'error' => $data['error_message'] ?? ('HTTP ' . $response->status() . ' ' . $body),
                'response' => $body,
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage(), 'response' => null];
        }
    }

    /**
     * Balance (in Taka) of the platform gateway account, or null if unavailable.
     */
    public static function platformBalance(): ?float
    {
        $key = Setting::smsApiKey();
        if (!$key) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get(config('services.sms.balance_url', 'http://bulksmsbd.net/api/getBalanceApi'), [
                'api_key' => $key,
            ]);

            if ($response->successful()) {
                $data = json_decode($response->body(), true);
                return isset($data['balance']) ? (float) $data['balance'] : null;
            }
        } catch (\Exception $e) {
            Log::error('Platform SMS balance check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Gateway account balance (in Taka) for the market's own API key.
     * Platform-gateway markets use prepaid credits instead; see Market::$sms_credits.
     */
    public function getBalance(): ?float
    {
        if (!$this->market || $this->usesPlatformGateway() || !$this->market->sms_api_key) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get(config('services.sms.balance_url', 'http://bulksmsbd.net/api/getBalanceApi'), [
                'api_key' => $this->market->sms_api_key,
            ]);

            if ($response->successful()) {
                $data = json_decode($response->body(), true);
                return $data['balance'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('SMS balance check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
