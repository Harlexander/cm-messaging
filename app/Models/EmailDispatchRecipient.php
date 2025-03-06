<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDispatchRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispatch_id',
        'email',
        'status',
        'delivered_at',
        'opened_at',
        'error',
        'unsubscribe_token',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_OPENED = 'opened';
    const STATUS_FAILED = 'failed';

    /**
     * Get the dispatch that owns this recipient.
     */
    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(EmailDispatch::class, 'dispatch_id');
    }

    /**
     * Get the user associated with this recipient.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the email as opened.
     */
    public function markOpened(): void
    {
        if ($this->status === self::STATUS_DELIVERED) {
            $this->update([
                'status' => self::STATUS_OPENED,
                'opened_at' => now(),
            ]);
        }
    }

    /**
     * Mark the email as failed with an error.
     */
    public function markFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error' => $error,
        ]);
    }
} 