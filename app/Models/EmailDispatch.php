<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailDispatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject',
        'message',
        'filters',
        'status',
        'sent_at',
        'completed_at',
        'error_log',
    ];

    protected $casts = [
        'filters' => 'array',
        'error_log' => 'array',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants 
    const STATUS_PENDING = 'pending';
    const STATUS_SENDING = 'sending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the recipients for this dispatch.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailDispatchRecipient::class, 'dispatch_id');
    }

    /**
     * Update the dispatch status and related timestamps.
     */
    public function updateStatus(string $status): void
    {
        $this->status = $status;
        
        if ($status === self::STATUS_SENDING) {
            $this->sent_at = now();
        } elseif (in_array($status, [self::STATUS_COMPLETED, self::STATUS_FAILED])) {
            $this->completed_at = now();
        }

        $this->save();
    }

    /**
     * Log an error for this dispatch.
     */
    public function logError(string $error): void
    {
        $errorLog = $this->error_log ?? [];
        $errorLog[] = [
            'timestamp' => now()->toDateTimeString(),
            'error' => $error,
        ];

        $this->error_log = $errorLog;
        $this->save();
    }
} 