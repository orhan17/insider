<?php

namespace App\Jobs;

use App\Contracts\CacheServiceInterface;
use App\Contracts\LogServiceInterface;
use App\Contracts\MessageServiceInterface;
use App\Contracts\WebhookServiceInterface;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        private readonly int $messageId,
        private readonly string $phoneNumber,
        private readonly string $content
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(
        WebhookServiceInterface $webhookService,
        MessageServiceInterface $messageService,
        CacheServiceInterface $cacheService,
        LogServiceInterface $logService
    ): void {
        $logService->info('Processing message', [
            'message_id' => $this->messageId,
            'phone' => $this->phoneNumber,
        ]);

        $result = $webhookService->sendMessage($this->phoneNumber, $this->content);

        if ($result->isSuccess()) {
            $messageService->markAsSent($this->messageId, $result->messageId);

            $cacheService->cacheMessageId(
                $this->messageId,
                $result->messageId,
                now()->toIso8601String()
            );

            $logService->info('Message sent successfully', [
                'message_id' => $this->messageId,
                'external_message_id' => $result->messageId,
            ]);
        } else {
            $messageService->markAsFailed($this->messageId);

            $logService->error('Failed to send message', [
                'message_id' => $this->messageId,
                'error' => $result->error,
            ]);

            throw new Exception($result->error);
        }
    }

    public function failed(Throwable $exception): void
    {
        app(LogServiceInterface::class)->error('Message job failed permanently', [
            'message_id' => $this->messageId,
            'phone' => $this->phoneNumber,
            'error' => $exception->getMessage(),
        ]);
    }
}
