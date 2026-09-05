<?php

namespace App\Models;

use App\Traits\BelongsToMarket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, BelongsToMarket;

    protected $fillable = [
        'invoice_id',
        'market_id',
        'shop_id',
        'collected_by',
        'amount',
        'payment_method',
        'receipt_number',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = static::generateReceiptNumber($payment->market_id);
            }
        });

        static::created(function ($payment) {
            // Update invoice paid amount
            $payment->invoice->recordPayment($payment->amount);
        });
    }

    public static function generateReceiptNumber(int $marketId): string
    {
        $prefix = 'RCP';
        $year = date('Y');
        $month = date('m');

        $lastPayment = static::withoutGlobalScopes()
            ->where('market_id', $marketId)
            ->where('receipt_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->receipt_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $newNumber);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
