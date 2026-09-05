<?php

namespace App\Models;

use App\Traits\BelongsToMarket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use HasFactory, BelongsToMarket;

    protected $fillable = [
        'market_id',
        'created_by',
        'title',
        'title_bn',
        'content',
        'content_bn',
        'target_role',
        'is_pinned',
        'expires_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where(function ($q) use ($role) {
            $q->where('target_role', 'all')
              ->orWhere('target_role', $role);
        });
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    public function getLocalizedTitle(): string
    {
        if (app()->getLocale() === 'bn' && $this->title_bn) {
            return $this->title_bn;
        }
        return $this->title;
    }

    public function getLocalizedContent(): string
    {
        if (app()->getLocale() === 'bn' && $this->content_bn) {
            return $this->content_bn;
        }
        return $this->content;
    }
}
