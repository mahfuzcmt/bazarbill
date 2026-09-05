<?php

namespace App\Models;

use App\Traits\BelongsToMarket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory, BelongsToMarket;

    protected $fillable = [
        'market_id',
        'recipient_phone',
        'message',
        'status',
        'api_response',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function markAsSent(string $response = null): void
    {
        $this->update([
            'status' => 'sent',
            'api_response' => $response,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $response = null): void
    {
        $this->update([
            'status' => 'failed',
            'api_response' => $response,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
