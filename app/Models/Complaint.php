<?php

namespace App\Models;

use App\Traits\BelongsToMarket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory, BelongsToMarket;

    protected $fillable = [
        'market_id',
        'shop_id',
        'submitted_by',
        'assigned_to',
        'subject',
        'description',
        'status',
        'priority',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function resolve(?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }

    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    public function assignTo(int $userId): void
    {
        $this->update([
            'assigned_to' => $userId,
            'status' => 'in_progress',
        ]);
    }

    public static function getPriorities(): array
    {
        return [
            'low' => __('complaints.priorities.low'),
            'medium' => __('complaints.priorities.medium'),
            'high' => __('complaints.priorities.high'),
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'open' => __('complaints.statuses.open'),
            'in_progress' => __('complaints.statuses.in_progress'),
            'resolved' => __('complaints.statuses.resolved'),
            'closed' => __('complaints.statuses.closed'),
        ];
    }
}
