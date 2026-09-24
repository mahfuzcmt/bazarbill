<?php

namespace App\Models;

use App\Traits\BelongsToMarket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory, BelongsToMarket;

    protected $fillable = [
        'market_id',
        'shop_owner_id',
        'collector_id',
        'shop_number',
        'floor',
        'area_sqft',
        'rent_amount',
        'advance_deposit',
        'shop_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'advance_deposit' => 'decimal:2',
        'area_sqft' => 'decimal:2',
    ];

    /**
     * Make $owner the owner of exactly $shopIds within the market: shops of theirs
     * not in the list are released; listed shops are taken only if unowned or already theirs.
     */
    public static function syncOwner(User $owner, array $shopIds, int $marketId): void
    {
        $shopIds = array_values(array_filter(array_map('intval', $shopIds)));

        static::withoutGlobalScopes()
            ->where('market_id', $marketId)
            ->where('shop_owner_id', $owner->id)
            ->whereNotIn('id', $shopIds ?: [0])
            ->update(['shop_owner_id' => null]);

        if ($shopIds) {
            static::withoutGlobalScopes()
                ->where('market_id', $marketId)
                ->whereIn('id', $shopIds)
                ->where(fn ($q) => $q->whereNull('shop_owner_id')->orWhere('shop_owner_id', $owner->id))
                ->update(['shop_owner_id' => $owner->id]);
        }
    }

    public function shopOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shop_owner_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isVacant(): bool
    {
        return $this->status === 'vacant';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function hasOwner(): bool
    {
        return $this->shop_owner_id !== null;
    }

    public function getTotalDue(): float
    {
        return $this->invoices()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('due_amount');
    }

    public function getLastPayment(): ?Payment
    {
        return $this->payments()->latest('payment_date')->first();
    }

    public function getDisplayName(): string
    {
        $name = $this->shop_number;
        if ($this->floor) {
            $name .= ' (' . $this->floor . ')';
        }
        return $name;
    }

    public static function getShopTypes(): array
    {
        return [
            'general' => __('shops.types.general'),
            'food' => __('shops.types.food'),
            'clothing' => __('shops.types.clothing'),
            'electronics' => __('shops.types.electronics'),
            'jewelry' => __('shops.types.jewelry'),
            'pharmacy' => __('shops.types.pharmacy'),
            'other' => __('shops.types.other'),
        ];
    }
}
