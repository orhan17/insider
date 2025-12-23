<?php

namespace App\Console\Commands;

use App\Contracts\LogServiceInterface;
use App\Contracts\MessageServiceInterface;
use App\Jobs\SendMessageJob;
use Illuminate\Console\Command;

class ProcessPendingMessagesCommand extends Command
{
    protected $signature = 'messages:process
                            {--limit=100 : Maximum number of messages to process}';

    protected $description = 'Process pending messages and dispatch them to queue with rate limiting';

    public function __construct(
        private readonly MessageServiceInterface $messageService,
        private readonly LogServiceInterface $logService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        /** @var int $rateLimit */
        $rateLimit = config('messages.rate_limit') ?? 2;
        /** @var int $rateInterval */
        $rateInterval = config('messages.rate_interval') ?? 5;

        $this->info("Starting to process pending messages...");
        $this->info("Rate limit: {$rateLimit} messages every {$rateInterval} seconds");

        $pendingMessages = $this->messageService->getPendingMessages($limit);

        if ($pendingMessages->isEmpty()) {
            $this->info('No pending messages found.');

            return self::SUCCESS;
        }

        $this->info("Found {$pendingMessages->count()} pending messages");

        $processed = 0;
        $messages = $pendingMessages->all();
        $batches = array_chunk($messages, $rateLimit);

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

            if ($batchIndex < count($batches) - 1) {
                $this->info("Waiting {$rateInterval} seconds before next batch...");
                sleep($rateInterval);
            }
        }

        $this->info("Successfully dispatched {$processed} messages to queue");
        $this->info("Run 'php artisan queue:work' to process the queue");

        $this->logService->info('Pending messages processed', ['count' => $processed]);

        return self::SUCCESS;
    }
}
