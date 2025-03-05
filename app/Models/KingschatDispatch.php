<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class KingschatDispatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
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

    protected $appends = [
        'total_recipients',
        'delivered_count',
        'read_count',
        'read_rate',
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
        return $this->hasMany(KingschatDispatchRecipient::class, 'dispatch_id');
    }

    /**
     * Get total number of recipients.
     */
    public function getTotalRecipientsAttribute(): int
    {
        return $this->getCachedMetric('total_recipients', function () {
            return $this->recipients()->count();
        });
    }

    /**
     * Get number of delivered messages.
     */
    public function getDeliveredCountAttribute(): int
    {
        return $this->getCachedMetric('delivered_count', function () {
            return $this->recipients()
                ->where('status', KingschatDispatchRecipient::STATUS_DELIVERED)
                ->count();
        });
    }

    /**
     * Get number of read messages.
     */
    public function getReadCountAttribute(): int
    {
        return $this->getCachedMetric('read_count', function () {
            return $this->recipients()
                ->where('status', KingschatDispatchRecipient::STATUS_READ)
                ->count();
        });
    }

    /**
     * Calculate read rate.
     */
    public function getReadRateAttribute(): float
    {
        if ($this->delivered_count === 0) {
            return 0;
        }

        return round(($this->read_count / $this->delivered_count) * 100, 2);
    }

    /**
     * Get demographics data for the dispatch.
     */
    public function getDemographics(): array
    {
        return $this->getCachedMetric('demographics', function () {
            return [
                'zones' => $this->recipients()
                    ->join('users', 'users.id', '=', 'kingschat_dispatch_recipients.user_id')
                    ->selectRaw('users.zone, count(*) as count')
                    ->groupBy('users.zone')
                    ->pluck('count', 'zone')
                    ->toArray(),
                'countries' => $this->recipients()
                    ->join('users', 'users.id', '=', 'kingschat_dispatch_recipients.user_id')
                    ->selectRaw('users.country, count(*) as count')
                    ->groupBy('users.country')
                    ->pluck('count', 'country')
                    ->toArray(),
            ];
        });
    }

    /**
     * Get engagement metrics for a specific period.
     */
    public function getEngagementMetrics(string $start, string $end): array
    {
        $recipients = $this->recipients()
            ->whereBetween('delivered_at', [$start, $end])
            ->get();

        $delivered = $recipients->count();
        $read = $recipients->where('status', KingschatDispatchRecipient::STATUS_READ)->count();

        return [
            'delivered' => $delivered,
            'read' => $read,
            'read_rate' => $delivered > 0 ? round(($read / $delivered) * 100, 2) : 0,
        ];
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
        $this->clearMetricsCache();
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

    /**
     * Check if the dispatch is complete.
     */
    public function isComplete(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED]);
    }

    /**
     * Get cached metric value or calculate it.
     */
    private function getCachedMetric(string $key, callable $callback)
    {
        $cacheKey = "dispatch:{$this->id}:{$key}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), $callback);
    }

    /**
     * Clear cached metrics.
     */
    private function clearMetricsCache(): void
    {
        $metrics = ['total_recipients', 'delivered_count', 'read_count', 'demographics'];
        
        foreach ($metrics as $metric) {
            Cache::forget("dispatch:{$this->id}:{$metric}");
        }
    }
} 