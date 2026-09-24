<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-wide settings the super admin edits from the UI.
 * Values override the matching .env defaults.
 */
class Setting extends Model
{
    public const CACHE_KEY = 'platform_settings';

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function all_(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            if (!Schema::hasTable('settings')) {
                return [];
            }

            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::all_()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    /* ------------------------------------------------------------ SMS gateway */

    public static function smsApiKey(): ?string
    {
        return self::get('sms.api_key', config('services.sms.api_key')) ?: null;
    }

    public static function smsSenderId(): string
    {
        return (string) self::get('sms.sender_id', config('services.sms.sender_id', '8809617642636'));
    }

    public static function smsLowCreditThreshold(): int
    {
        return (int) self::get('sms.low_credit_threshold', config('services.sms.low_credit_threshold', 20));
    }
}
