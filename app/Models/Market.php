<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_bn',
        'slug',
        'address',
        'address_bn',
        'phone',
        'email',
        'logo',
        'sms_api_key',
        'sms_sender_id',
        'sms_templates',
        'settings',
        'status',
    ];

    protected $casts = [
        'sms_templates' => 'array',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($market) {
            if (empty($market->slug)) {
                $market->slug = Str::slug($market->name);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getLocalizedName(): string
    {
        if (app()->getLocale() === 'bn' && $this->name_bn) {
            return $this->name_bn;
        }
        return $this->name;
    }

    public function getLocalizedAddress(): ?string
    {
        if (app()->getLocale() === 'bn' && $this->address_bn) {
            return $this->address_bn;
        }
        return $this->address;
    }

    public function getSmsTemplate(string $type): ?string
    {
        return $this->sms_templates[$type] ?? null;
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function getDefaultSmsTemplates(): array
    {
        return [
            'invoice_generated' => 'প্রিয় {shop_owner}, আপনার {month} মাসের ভাড়া {amount} টাকা। বিল নং: {invoice_no}',
            'payment_reminder' => 'প্রিয় {shop_owner}, আপনার {amount} টাকা বকেয়া আছে। অনুগ্রহ করে পরিশোধ করুন।',
            'payment_received' => 'ধন্যবাদ! {amount} টাকা পেমেন্ট গৃহীত হয়েছে। রসিদ নং: {receipt_no}',
        ];
    }
}
