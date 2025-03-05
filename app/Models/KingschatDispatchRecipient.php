<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KingschatDispatchRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispatch_id',
        'kc_user_id',
        'status',
        'delivered_at',
        'read_at',
        'error',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';

    /**
     * Get the dispatch that owns this recipient.
     */
    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(KingschatDispatch::class);
    }

    /**
     * Get the user that owns this recipient.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the message as delivered.
     */
    public function markDelivered(): void
    {
        if ($this->status === self::STATUS_PENDING) {
            $this->update([
                'status' => self::STATUS_DELIVERED,
                'delivered_at' => now(),
            ]);
        }
    }

    /**
     * Mark the message as read.
     */
    public function markRead(): void
    {
        if ($this->status === self::STATUS_DELIVERED) {
            $this->update([
                'status' => self::STATUS_READ,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Mark the message as failed with an error.
     */
    public function markFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error' => $error,
        ]);
    }
} 