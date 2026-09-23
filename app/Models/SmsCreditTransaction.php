<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsCreditTransaction extends Model
{
    use HasFactory;

    public const TYPE_SUBSCRIPTION = 'subscription';
    public const TYPE_RECHARGE = 'recharge';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_USAGE = 'usage';
    public const TYPE_REFUND = 'refund';

    /** Types a super admin may record by hand. */
    public const MANUAL_TYPES = [
        self::TYPE_SUBSCRIPTION,
        self::TYPE_RECHARGE,
        self::TYPE_ADJUSTMENT,
    ];

    protected $fillable = [
        'market_id',
        'type',
        'amount',
        'balance_after',
        'reference',
        'note',
        'created_by',
        'sms_log_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
    ];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function smsLog(): BelongsTo
    {
        return $this->belongsTo(SmsLog::class);
    }

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }
}
