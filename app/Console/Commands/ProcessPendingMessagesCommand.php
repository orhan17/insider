<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageJob;
use App\Services\MessageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPendingMessagesCommand extends Command
{
    protected $signature = 'messages:process
                            {--limit=100 : Maximum number of messages to process}';

    protected $description = 'Process pending messages and dispatch them to queue with rate limiting';

    public function handle(MessageService $messageService): int
    {
        $limit = (int) $this->option('limit');

        /** @var int $rateLimit */
        $rateLimit = config('messages.rate_limit') ?? 2;
        /** @var int $rateInterval */
        $rateInterval = config('messages.rate_interval') ?? 5;

        $this->info("Starting to process pending messages...");
        $this->info("Rate limit: {$rateLimit} messages every {$rateInterval} seconds");

        $pendingMessages = $messageService->getPendingMessages($limit);

        if ($pendingMessages->isEmpty()) {
            $this->info('No pending messages found.');

            return self::SUCCESS;
        }

        $this->info("Found {$pendingMessages->count()} pending messages");

        $processed = 0;
        $batches = $pendingMessages->chunk($rateLimit);

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $message) {
                SendMessageJob::dispatch(
                    $message->id,
                    $message->phone_number,
                    $message->content
                );

                $processed++;
                $this->info("Dispatched message #{$message->id} to queue");
            }

            if ($batchIndex < $batches->count() - 1) {
                $this->info("Waiting {$rateInterval} seconds before next batch...");
                sleep($rateInterval);
            }
        }

        $this->info("Successfully dispatched {$processed} messages to queue");
        $this->info("Run 'php artisan queue:work' to process the queue");

        Log::info('Pending messages processed', ['count' => $processed]);

        return self::SUCCESS;
    }
}
