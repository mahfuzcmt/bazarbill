<?php

namespace App\Services;

use App\Models\Market;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $apiUrl = 'http://bulksmsbd.net/api/smsapi';

    public function __construct(
        protected ?Market $market = null
    ) {}

    public function setMarket(Market $market): self
    {
        $this->market = $market;
        return $this;
    }

    public function send(string $phone, string $message, bool $isUnicode = true): bool
    {
        if (!$this->market) {
            throw new \RuntimeException('Market not set for SMS service');
        }

        if (!$this->market->sms_api_key) {
            Log::warning('SMS API key not configured for market', ['market_id' => $this->market->id]);
            return false;
        }

        // Normalize phone number
        $phone = $this->normalizePhone($phone);

        // Create SMS log
        $smsLog = SmsLog::create([
            'market_id' => $this->market->id,
            'recipient_phone' => $phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        try {
            $response = Http::timeout(30)->get($this->apiUrl, [
                'api_key' => $this->market->sms_api_key,
                'senderid' => $this->market->sms_sender_id ?? '8809617642636',
                'number' => $phone,
                'message' => $message,
                'type' => $isUnicode ? 'unicode' : 'text',
            ]);

            $responseBody = $response->body();

            if ($response->successful()) {
                // Parse response - bulksmsbd.net returns JSON with response_code
                $responseData = json_decode($responseBody, true);

                if (isset($responseData['response_code']) && $responseData['response_code'] == 202) {
                    $smsLog->markAsSent($responseBody);
                    return true;
                }

                $smsLog->markAsFailed($responseBody);
                Log::error('SMS send failed', [
                    'market_id' => $this->market->id,
                    'phone' => $phone,
                    'response' => $responseBody,
                ]);
                return false;
            }

            $smsLog->markAsFailed($responseBody);
            return false;

        } catch (\Exception $e) {
            $smsLog->markAsFailed($e->getMessage());
            Log::error('SMS send exception', [
                'market_id' => $this->market->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
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

    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 88, return as is
        if (str_starts_with($phone, '88')) {
            return $phone;
        }

        // If starts with 0, replace with 88
        if (str_starts_with($phone, '0')) {
            return '88' . substr($phone, 1);
        }

        // Otherwise, prepend 88
        return '88' . $phone;
    }

    public function getBalance(): ?float
    {
        if (!$this->market || !$this->market->sms_api_key) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get('http://bulksmsbd.net/api/getBalanceApi', [
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
