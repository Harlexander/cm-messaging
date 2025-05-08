<?php

namespace App\Console\Commands;

use App\Jobs\SendKingsChatMessage;
use App\Models\KingschatDispatch;
use App\Models\KingschatDispatchRecipient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestKingsChatRateLimit extends Command
{
    protected $signature = 'kingschat:test-limit 
                            {batch=10 : How many messages to send in this test}
                            {delay=1 : Delay between messages in seconds}
                            {message=test : Message to send}';
    
    protected $description = 'Test KingsChat rate limits by sending multiple messages';

    public function handle(): int
    {
        $delay = $this->argument('delay');
        $batchSize = $this->argument('batch');
        $message = $this->argument('message');
        $userId = '6315d07c0db4407b4ee63627'; // Your test user ID

        $this->info("Starting rate limit test:");
        $this->info("- Sending {$batchSize} messages");
        $this->info("- {$delay} second(s) delay between messages");
        $this->info("- To user ID: {$userId}");

        // Create a test dispatch
        $dispatch = KingschatDispatch::create([
            'title' => 'Rate Limit Test',
            'message' => $message,
            'status' => 'processing',
            'filters' => [
                'designation' => 'all',
                'zone' => 'all',
                'country' => 'all'
            ],
            'sent_at' => now()
        ]);

        $bar = $this->output->createProgressBar($batchSize);
        $successCount = 0;
        $failureCount = 0;
        $firstError = null;

        for ($i = 0; $i < $batchSize; $i++) {
            try {
                // Create recipient record
                $recipient = KingschatDispatchRecipient::create([
                    'dispatch_id' => $dispatch->id,
                    'kc_user_id' => $userId,
                    'status' => 'pending'
                ]);

                // Dispatch immediately (don't queue)
                SendKingsChatMessage::dispatchSync($dispatch, $recipient);

                if ($recipient->fresh()->status === 'delivered') {
                    $successCount++;
                } else {
                    $failureCount++;
                    if (!$firstError && $recipient->error) {
                        $firstError = $recipient->error;
                    }
                }

                $bar->advance();

                if ($delay > 0) {
                    sleep($delay);
                }

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed at message #" . ($i + 1) . ": " . $e->getMessage());
                if (!$firstError) {
                    $firstError = $e->getMessage();
                }
                $failureCount++;
                break;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Test completed:");
        $this->info("- Successful messages: {$successCount}");
        $this->info("- Failed messages: {$failureCount}");
        if ($firstError) {
            $this->info("- First error encountered: {$firstError}");
        }

        // Log the results
        Log::info('KingsChat rate limit test results', [
            'batch_size' => $batchSize,
            'delay' => $delay,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'first_error' => $firstError,
            'dispatch_id' => $dispatch->id
        ]);

        return Command::SUCCESS;
    }
} 