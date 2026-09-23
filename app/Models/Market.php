<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'sms_credits',
        'settings',
        'status',
        'plan_id',
        'subscription_status',
        'subscription_ends_at',
    ];

    protected $casts = [
        'sms_templates' => 'array',
        'settings' => 'array',
        'sms_credits' => 'integer',
        'subscription_ends_at' => 'date',
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

    public function smsCreditTransactions(): HasMany
    {
        return $this->hasMany(SmsCreditTransaction::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * The subscription period that is live today, if any.
     */
    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', [Subscription::STATUS_TRIAL, Subscription::STATUS_ACTIVE])
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->orderByDesc('ends_at')
            ->first();
    }

    /**
     * Markets that were never put on a plan (legacy rows) are not blocked;
     * once a plan is assigned the end date is enforced.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_ends_at === null) {
            return true;
        }

        return in_array($this->subscription_status, [Subscription::STATUS_TRIAL, Subscription::STATUS_ACTIVE], true)
            && $this->subscription_ends_at->endOfDay()->isFuture();
    }

    public function isOnTrial(): bool
    {
        return $this->subscription_status === Subscription::STATUS_TRIAL && $this->hasActiveSubscription();
    }

    public function subscriptionDaysRemaining(): ?int
    {
        if ($this->subscription_ends_at === null) {
            return null;
        }

        return max(0, (int) today()->diffInDays($this->subscription_ends_at, false));
    }

    public function shopLimit(): ?int
    {
        return $this->plan?->shop_limit;
    }

    public function canAddShop(): bool
    {
        $limit = $this->shopLimit();

        return $limit === null || $this->shops()->count() < $limit;
    }

    public function planAllows(string $feature): bool
    {
        // No plan assigned = legacy market, nothing is gated.
        return $this->plan === null || $this->plan->hasFeature($feature);
    }

    /**
     * Markets without their own gateway key send through the platform
     * account and consume prepaid SMS credits.
     */
    public function usesPlatformSms(): bool
    {
        return empty($this->sms_api_key);
    }

    public function hasLowSmsCredits(): bool
    {
        return $this->usesPlatformSms()
            && $this->sms_credits <= (int) config('services.sms.low_credit_threshold', 20);
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
