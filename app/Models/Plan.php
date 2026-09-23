<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory;

    /** Feature flags a plan can carry, with their labels for the admin form. */
    public const FEATURES = [
        'masking_sms' => 'Branded (masking) SMS sender ID',
        'pdf_reports' => 'PDF report exports',
        'complaints' => 'Complaint management',
        'notices' => 'Notice board',
    ];

    protected $fillable = [
        'name',
        'name_bn',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'shop_limit',
        'sms_credits_per_month',
        'trial_days',
        'trial_sms_credits',
        'features',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'shop_limit' => 'integer',
        'sms_credits_per_month' => 'integer',
        'trial_days' => 'integer',
        'trial_sms_credits' => 'integer',
        'features' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function markets(): HasMany
    {
        return $this->hasMany(Market::class);
    }

    public static function default(): ?self
    {
        return static::where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order')->orderBy('monthly_price')->first();
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    public function isUnlimitedShops(): bool
    {
        return $this->shop_limit === null;
    }

    public function offersYearly(): bool
    {
        return $this->yearly_price !== null;
    }

    public function priceFor(string $cycle): float
    {
        return $cycle === 'yearly' && $this->offersYearly()
            ? (float) $this->yearly_price
            : (float) $this->monthly_price;
    }

    public function getLocalizedName(): string
    {
        if (app()->getLocale() === 'bn' && $this->name_bn) {
            return $this->name_bn;
        }
        return $this->name;
    }
}
