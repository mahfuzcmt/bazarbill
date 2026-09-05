<?php

namespace App\Traits;

use App\Models\Market;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToMarket
{
    protected static function bootBelongsToMarket(): void
    {
        // Auto-scope queries to current market
        static::addGlobalScope('market', function (Builder $builder) {
            if (auth()->check() && auth()->user()->market_id) {
                $builder->where($builder->getModel()->getTable() . '.market_id', auth()->user()->market_id);
            }
        });

        // Auto-set market_id on creation
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->market_id && !$model->market_id) {
                $model->market_id = auth()->user()->market_id;
            }
        });
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function scopeForMarket(Builder $query, int $marketId): Builder
    {
        return $query->withoutGlobalScope('market')->where('market_id', $marketId);
    }
}
