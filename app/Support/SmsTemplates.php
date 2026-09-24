<?php

namespace App\Support;

use App\Models\Market;
use App\Models\Setting;

/**
 * The three SMS texts the system sends, with a three-level override chain:
 *   market's own template  →  platform default (Admin > SMS Gateway)  →  built-in default.
 */
class SmsTemplates
{
    public const TYPES = ['invoice_generated', 'payment_reminder', 'payment_received'];

    /** Placeholders available to every template. */
    public const COMMON_PLACEHOLDERS = ['shop_owner', 'shop_no', 'market'];

    /** Placeholders specific to each template type. */
    public const PLACEHOLDERS = [
        'invoice_generated' => ['month', 'amount', 'invoice_no', 'due_date'],
        'payment_reminder' => ['amount', 'due_date'],
        'payment_received' => ['amount', 'receipt_no', 'due_amount'],
    ];

    public const MAX_LENGTH = 300;

    public static function builtIn(): array
    {
        return [
            'invoice_generated' => 'প্রিয় {shop_owner}, {market}-এ আপনার দোকান {shop_no}-এর {month} মাসের ভাড়া {amount} টাকা। বিল নং: {invoice_no}',
            'payment_reminder' => 'প্রিয় {shop_owner}, দোকান {shop_no}-এর {amount} টাকা বকেয়া আছে। অনুগ্রহ করে পরিশোধ করুন। - {market}',
            'payment_received' => 'ধন্যবাদ {shop_owner}! দোকান {shop_no}-এর {amount} টাকা পেমেন্ট গৃহীত হয়েছে। রসিদ নং: {receipt_no}। অবশিষ্ট বকেয়া: {due_amount} টাকা। - {market}',
        ];
    }

    public static function placeholdersFor(string $type): array
    {
        return array_merge(self::COMMON_PLACEHOLDERS, self::PLACEHOLDERS[$type] ?? []);
    }

    /** Platform-wide default set by the super admin, or the built-in text. */
    public static function platformDefault(string $type): string
    {
        return (string) Setting::get('sms.template.' . $type, self::builtIn()[$type] ?? '');
    }

    /** The text a given market will actually send. */
    public static function resolve(Market $market, string $type): string
    {
        $own = trim((string) ($market->sms_templates[$type] ?? ''));

        return $own !== '' ? $own : self::platformDefault($type);
    }

    public static function render(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }

    /** Example values used for previews on the settings screens. */
    public static function sampleData(): array
    {
        return [
            'shop_owner' => 'করিম উদ্দিন', 'shop_no' => '২৭', 'market' => 'নিউ মার্কেট',
            'month' => 'অক্টোবর ২০২৬', 'amount' => '8,500', 'invoice_no' => 'INV-202610-0027',
            'due_date' => '10 Oct 2026', 'receipt_no' => 'RCP-202610-0031', 'due_amount' => '0',
        ];
    }
}
