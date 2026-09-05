<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'market_id',
        'name',
        'name_bn',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'is_active',
        'language_preference',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class, 'shop_owner_id');
    }

    public function assignedShops(): HasMany
    {
        return $this->hasMany(Shop::class, 'collector_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'collected_by');
    }

    public function submittedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'submitted_by');
    }

    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isMarketOwner(): bool
    {
        return $this->role === 'market_owner';
    }

    public function isCollector(): bool
    {
        return $this->role === 'collector';
    }

    public function isShopOwner(): bool
    {
        return $this->role === 'shop_owner';
    }

    public function getLocalizedName(): string
    {
        if (app()->getLocale() === 'bn' && $this->name_bn) {
            return $this->name_bn;
        }
        return $this->name;
    }

    public function getPreferredLocale(): string
    {
        return $this->language_preference ?? 'bn';
    }
}
