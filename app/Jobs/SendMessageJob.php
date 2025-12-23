<?php

namespace App\Jobs;

use App\Services\CacheService;
use App\Services\MessageService;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    public function __construct(
        private readonly int $messageId,
        private readonly string $phoneNumber,
        private readonly string $content
    ) {
    }

    public function handle(
        WebhookService $webhookService,
        MessageService $messageService,
        CacheService $cacheService
    ): void {
        Log::info('Processing message', [
            'message_id' => $this->messageId,
            'phone' => $this->phoneNumber,
        ]);

        $result = $webhookService->sendMessage($this->phoneNumber, $this->content);

        if ($result['success']) {
            $messageService->markAsSent($this->messageId, $result['messageId']);

            $cacheService->cacheMessageId(
                $this->messageId,
                $result['messageId'],
                now()->toIso8601String()
            );

            Log::info('Message sent successfully', [
                'message_id' => $this->messageId,
                'external_message_id' => $result['messageId'],
            ]);
        } else {
            $messageService->markAsFailed($this->messageId);

            Log::error('Failed to send message', [
                'message_id' => $this->messageId,
                'error' => $result['error'],
            ]);

            throw new \Exception($result['error']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Message job failed permanently', [
            'message_id' => $this->messageId,
            'phone' => $this->phoneNumber,
            'error' => $exception->getMessage(),
        ]);
    }
}
