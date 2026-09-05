<?php

namespace App\Models;

use App\Traits\BelongsToMarket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, BelongsToMarket;

    protected $fillable = [
        'market_id',
        'shop_id',
        'invoice_number',
        'billing_month',
        'rent_amount',
        'previous_due',
        'discount',
        'late_fee',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'due_date',
        'generated_at',
        'notes',
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'previous_due' => 'decimal:2',
        'discount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'due_date' => 'date',
        'generated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber($invoice->market_id);
            }
            if (empty($invoice->generated_at)) {
                $invoice->generated_at = now();
            }
        });
    }

    public static function generateInvoiceNumber(int $marketId): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        $lastInvoice = static::withoutGlobalScopes()
            ->where('market_id', $marketId)
            ->where('invoice_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $newNumber);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'due_amount' => 0,
        ]);
    }

    public function recordPayment(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->due_amount = max(0, $this->total_amount - $this->paid_amount);

        if ($this->due_amount <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }

        $this->save();
    }

    public function checkAndUpdateOverdue(): void
    {
        if ($this->status !== 'paid' && $this->due_date < now()->toDateString()) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function getBillingMonthFormatted(): string
    {
        return Carbon::createFromFormat('Y-m', $this->billing_month)->format('F Y');
    }

    public function getBillingMonthFormattedBn(): string
    {
        $months = [
            '01' => 'জানুয়ারি', '02' => 'ফেব্রুয়ারি', '03' => 'মার্চ',
            '04' => 'এপ্রিল', '05' => 'মে', '06' => 'জুন',
            '07' => 'জুলাই', '08' => 'আগস্ট', '09' => 'সেপ্টেম্বর',
            '10' => 'অক্টোবর', '11' => 'নভেম্বর', '12' => 'ডিসেম্বর',
        ];

        [$year, $month] = explode('-', $this->billing_month);
        $bnYear = self::convertToBengaliNumber($year);

        return $months[$month] . ' ' . $bnYear;
    }

    protected static function convertToBengaliNumber(string $number): string
    {
        $bengaliNumbers = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($englishNumbers, $bengaliNumbers, $number);
    }
}
