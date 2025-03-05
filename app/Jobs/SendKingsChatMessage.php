<?php

namespace App\Jobs;

use App\Models\KingschatDispatch;
use App\Models\KingschatDispatchRecipient;
use App\Services\KingsChatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendKingsChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;
    public int $backoff = 60; // Wait 60 seconds between retries

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected KingschatDispatch $dispatch,
        protected KingschatDispatchRecipient $recipient
    ) {}

    /**
     * Execute the job.
     */
    public function handle(KingsChatService $kingsChatService): void
    {
        // Skip if already delivered or has error
        if ($this->recipient->status !== 'pending') {
            return;
        }

        $result = $kingsChatService->sendMessage(
            $this->recipient->kc_user_id,
            $this->dispatch->message
        );

        if ($result['success']) {
            $this->recipient->update([
                'status' => 'delivered',
                'delivered_at' => now()
            ]);
        } else {
            $this->recipient->update([
                'status' => 'failed',
                'error' => $result['error']
            ]);

            // If we've hit max retries, mark as permanently failed
            if ($this->attempts() >= $this->tries) {
                $this->recipient->update([
                    'status' => 'failed',
                    'error' => 'Max retries exceeded: ' . $result['error']
                ]);
            } else {
                // Otherwise, throw exception to trigger retry
                throw new \Exception($result['error']);
            }
        }
    }
} 