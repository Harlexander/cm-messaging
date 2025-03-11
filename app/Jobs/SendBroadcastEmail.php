<?php

namespace App\Jobs;

use App\Mail\BroadcastMail;
use App\Models\EmailDispatch;
use App\Models\EmailDispatchRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendBroadcastEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;
    public int $backoff = 60; // Wait 60 seconds between retries

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected EmailDispatch $dispatch,
        protected EmailDispatchRecipient $recipient,
        protected string $name
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip if already sent or has error
        if ($this->recipient->status !== 'pending') {
            return;
        }

        try {
            // Send the email
            Mail::to($this->recipient->email)
                ->send(new BroadcastMail(
                    [
                        'subject' => $this->dispatch->subject,
                        'message' => $this->dispatch->message,
                        'name' => $this->name
                        // 'bannerImage' => "https://cdn1.kingschat.online/uploads/media/5cb67c0e6eb1170001f207fd/S2M2OE9CWTkxVFRnTnZDT0lPS2ZSQT09/No_One_Forgotten_live.jpg"
                    ]
                ));

            // Get the message ID from Brevo's response
            $messageId = null;
            if (config('mail.default') === 'brevo') {
                $messageId = session('brevo_message_id');
            }

            // Update recipient status
            $this->recipient->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'message_id' => $messageId
            ]);

        } catch (Exception $e) {
            // Update recipient status with error
            $this->recipient->update([
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);

            // If we've hit max retries, mark as permanently failed
            if ($this->attempts() >= $this->tries) {
                $this->recipient->update([
                    'status' => 'failed',
                    'error' => 'Max retries exceeded: ' . $e->getMessage()
                ]);
            } else {
                // Otherwise, throw exception to trigger retry
                throw $e;
            }
        }
    }
} 