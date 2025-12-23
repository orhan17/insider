<?php

namespace Tests\Unit;

use App\Jobs\SendMessageJob;
use App\Services\CacheService;
use App\Services\MessageService;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SendMessageJobTest extends TestCase
{
    public function test_job_sends_message_successfully(): void
    {
        $webhookService = Mockery::mock(WebhookService::class);
        $messageService = Mockery::mock(MessageService::class);
        $cacheService = Mockery::mock(CacheService::class);

        $webhookService->shouldReceive('sendMessage')
            ->once()
            ->with('+905551111111', 'Test message')
            ->andReturn([
                'success' => true,
                'messageId' => 'test-message-id-123',
            ]);

        $messageService->shouldReceive('markAsSent')
            ->once()
            ->with(1, 'test-message-id-123')
            ->andReturn(true);

        $cacheService->shouldReceive('cacheMessageId')
            ->once()
            ->with(1, 'test-message-id-123', Mockery::type('string'))
            ->andReturn(true);

        $this->app->instance(WebhookService::class, $webhookService);
        $this->app->instance(MessageService::class, $messageService);
        $this->app->instance(CacheService::class, $cacheService);

        $job = new SendMessageJob(1, '+905551111111', 'Test message');
        $job->handle($webhookService, $messageService, $cacheService);

        $this->assertTrue(true); // Assert the job executed without throwing exception
    }

    public function test_job_marks_message_as_failed_on_error(): void
    {
        $webhookService = Mockery::mock(WebhookService::class);
        $messageService = Mockery::mock(MessageService::class);
        $cacheService = Mockery::mock(CacheService::class);

        $webhookService->shouldReceive('sendMessage')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'Connection failed',
            ]);

        $messageService->shouldReceive('markAsFailed')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->app->instance(WebhookService::class, $webhookService);
        $this->app->instance(MessageService::class, $messageService);
        $this->app->instance(CacheService::class, $cacheService);

        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);

        $job = new SendMessageJob(1, '+905551111111', 'Test message');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Connection failed');

        $job->handle($webhookService, $messageService, $cacheService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
