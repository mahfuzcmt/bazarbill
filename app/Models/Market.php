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

    /** Users currently working in this market (users.market_id). */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Everyone with membership of this market, whichever market they are working in now. */
    public function members(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /** Owner-level accounts (market owners / managers) of this market. */
    public function managers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->members()->where('users.role', 'market_owner');
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
            && $this->sms_credits <= Setting::smsLowCreditThreshold();
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

    /** The market's own override for a template type, if it set one. */
    public function getSmsTemplate(string $type): ?string
    {
        $own = trim((string) ($this->sms_templates[$type] ?? ''));

        return $own !== '' ? $own : null;
    }

    /** The template text this market actually sends (own → platform default → built-in). */
    public function resolveSmsTemplate(string $type): string
    {
        return \App\Support\SmsTemplates::resolve($this, $type);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function getDefaultSmsTemplates(): array
    {
        return \App\Support\SmsTemplates::builtIn();
    }
}
