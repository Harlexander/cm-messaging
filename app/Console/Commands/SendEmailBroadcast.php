<?php

namespace App\Console\Commands;

use App\Jobs\SendBroadcastEmail;
use App\Models\EmailDispatch;
use App\Models\EmailDispatchRecipient;
use App\Models\UserList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendEmailBroadcast extends Command
{
    protected $signature = 'email:broadcast';
    protected $description = 'Process pending email broadcasts';

    public function handle(): int
    {
        // Get either a pending dispatch or a processing dispatch
        $dispatch = EmailDispatch::where('status', 'pending')
            ->orWhere('status', 'processing')
            ->orderBy('created_at')
            ->first();

        if (!$dispatch) {
            $this->info('No broadcasts to process.');
            return Command::SUCCESS;
        }

        $this->info("Processing broadcast ID: {$dispatch->id}");
        $this->info("Subject: {$dispatch->subject}");
        $this->info("Status: {$dispatch->status}");

        try {
            // Start transaction
            DB::beginTransaction();

            // Update dispatch status to processing if it was pending
            if ($dispatch->status === 'pending') {
                $dispatch->updateStatus('processing');
            }

            // Check how many emails are currently in queue (pending status)
            $queuedCount = EmailDispatchRecipient::where('dispatch_id', $dispatch->id)
                ->where('status', 'pending')
                ->count();

            if ($queuedCount >= 100) {
                $this->info("Already have {$queuedCount} emails in queue. Waiting for them to process...");
                DB::commit();
                return Command::SUCCESS;
            }

            // Get filtered users who haven't been queued for this email yet
            $query = UserList::query()
                ->whereNotNull('email')
                ->whereNotExists(function ($query) use ($dispatch) {
                    $query->select(DB::raw(1))
                        ->from('email_dispatch_recipients')
                        ->whereColumn('email_dispatch_recipients.email', 'prayer_conference.email')
                        ->where('email_dispatch_recipients.dispatch_id', $dispatch->id);
                });

            // Apply filters
            if($dispatch->filters['zone'] !== 'all') {
                $query->where('zone', $dispatch->filters['zone']);
            }
            if($dispatch->filters['designation'] !== 'all') {
                $query->where('designation', $dispatch->filters['designation']);
            }
            if($dispatch->filters['country'] !== 'all') {
                $query->where('country', $dispatch->filters['country']);
            }

            // Get total remaining users
            $remainingUsers = $query->count();
            
            if ($remainingUsers === 0) {
                // Double check if we have any pending emails
                if ($queuedCount === 0) {
                    $this->info('All recipients have been processed.');
                    $dispatch->updateStatus('completed');
                } else {
                    $this->info("Waiting for {$queuedCount} queued emails to complete...");
                }
                DB::commit();
                return Command::SUCCESS;
            }

            // Calculate how many more we can queue
            $batchSize = min(100 - $queuedCount, $remainingUsers);
            
            $this->info("Total remaining recipients: {$remainingUsers}");
            $this->info("Currently in queue: {$queuedCount}");
            $this->info("Processing batch of: {$batchSize}");
            
            // Create progress bar for this batch
            $bar = $this->output->createProgressBar($batchSize);

            // Process next batch of users
            $query->orderBy('id')
                ->take($batchSize)
                ->get()
                ->each(function ($user) use ($dispatch, $bar) {
                    // Create recipient record with unsubscribe token
                    $recipient = EmailDispatchRecipient::create([
                        'dispatch_id' => $dispatch->id,
                        'email' => $user->email,
                        'status' => 'pending',
                        'unsubscribe_token' => Str::random(32),
                    ]);

                    // Queue the email
                    SendBroadcastEmail::dispatch($dispatch, $recipient)
                        ->onQueue('emails');

                    $bar->advance();
                });

            $bar->finish();
            $this->newLine();

            DB::commit();

            // Only count users that haven't been queued yet
            $remainingAfterBatch = $query->count();
            $this->info("Batch queued successfully. {$remainingAfterBatch} recipients remaining to queue.");

            if ($remainingAfterBatch === 0 && $queuedCount === 0) {
                $dispatch->updateStatus('completed');
                $this->info('Broadcast completed successfully!');
            } else {
                $this->info('More recipients to process in next run.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to process broadcast: ' . $e->getMessage());
            
            // Update dispatch status to failed
            $dispatch->updateStatus('failed');
            $dispatch->logError($e->getMessage());

            return Command::FAILURE;
        }
    }
} 