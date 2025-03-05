<?php

namespace App\Console\Commands;

use App\Jobs\SendKingsChatMessage;
use App\Models\KingschatDispatch;
use App\Models\KingschatDispatchRecipient;
use App\Models\UserList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendKingsChatBroadcast extends Command
{
    protected $signature = 'kingschat:broadcast';
    protected $description = 'Process pending KingsChat broadcasts';

    public function handle(): int
    {
        // Get either a pending dispatch or a processing dispatch
        $dispatch = KingschatDispatch::where('status', 'pending')
            ->orWhere('status', 'processing')
            ->orderBy('created_at')
            ->first();

        if (!$dispatch) {
            $this->info('No broadcasts to process.');
            return Command::SUCCESS;
        }

        $this->info("Processing broadcast ID: {$dispatch->id}");
        $this->info("Title: {$dispatch->title}");
        $this->info("Status: {$dispatch->status}");

        try {
            // Start transaction
            DB::beginTransaction();

            // Update dispatch status to processing if it was pending
            if ($dispatch->status === 'pending') {
                $dispatch->update([
                    'status' => 'processing',
                    'sent_at' => now()
                ]);
            }

            // Check how many messages are currently in queue (pending status)
            $queuedCount = KingschatDispatchRecipient::where('dispatch_id', $dispatch->id)
                ->where('status', 'pending')
                ->count();

            if ($queuedCount >= 100) {
                $this->info("Already have {$queuedCount} messages in queue. Waiting for them to process...");
                DB::commit();
                return Command::SUCCESS;
            }

            // Get filtered users who haven't been queued for this message yet
            $query = UserList::query()
                ->whereNotNull('kc_user_id')
                ->whereNotExists(function ($query) use ($dispatch) {
                    $query->select(DB::raw(1))
                        ->from('kingschat_dispatch_recipients')
                        ->whereColumn('kingschat_dispatch_recipients.kc_user_id', 'prayer_conference.kc_user_id')
                        ->where('kingschat_dispatch_recipients.dispatch_id', $dispatch->id);
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
                // Double check if we have any pending messages
                if ($queuedCount === 0) {
                    $this->info('All recipients have been processed.');
                    $dispatch->update([
                        'status' => 'completed',
                        'completed_at' => now()
                    ]);
                } else {
                    $this->info("Waiting for {$queuedCount} queued messages to complete...");
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
                    // Create recipient record
                    $recipient = KingschatDispatchRecipient::create([
                        'dispatch_id' => $dispatch->id,
                        'kc_user_id' => $user->kc_user_id,
                        'status' => 'pending',
                    ]);

                    // Queue the message
                    SendKingsChatMessage::dispatch($dispatch, $recipient)
                        ->onQueue('kingschat');

                    $bar->advance();
                });

            $bar->finish();
            $this->newLine();

            DB::commit();

            // Only count users that haven't been queued yet
            $remainingAfterBatch = $query->count();
            $this->info("Batch queued successfully. {$remainingAfterBatch} recipients remaining to queue.");

            if ($remainingAfterBatch === 0 && $queuedCount === 0) {
                $dispatch->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
                $this->info('Broadcast completed successfully!');
            } else {
                $this->info('More recipients to process in next run.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to process broadcast: ' . $e->getMessage());
            
            // Update dispatch status to failed
            $dispatch->update([
                'status' => 'failed',
                'error_log' => array_merge($dispatch->error_log ?? [], [
                    [
                        'timestamp' => now()->toDateTimeString(),
                        'error' => $e->getMessage()
                    ]
                ])
            ]);

            return Command::FAILURE;
        }
    }
} 