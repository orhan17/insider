<?php

namespace Tests\Unit;

use App\Contracts\CacheServiceInterface;
use App\Contracts\LogServiceInterface;
use App\Contracts\MessageServiceInterface;
use App\Contracts\WebhookServiceInterface;
use App\DTO\WebhookResponseDTO;
use App\Jobs\SendMessageJob;
use Mockery;
use Tests\TestCase;

class SendMessageJobTest extends TestCase
{
    public function test_job_sends_message_successfully(): void
    {
        $webhookService = Mockery::mock(WebhookServiceInterface::class);
        $messageService = Mockery::mock(MessageServiceInterface::class);
        $cacheService = Mockery::mock(CacheServiceInterface::class);
        $logService = Mockery::mock(LogServiceInterface::class);

        $webhookService->shouldReceive('sendMessage')
            ->once()
            ->with('+905551111111', 'Test message')
            ->andReturn(WebhookResponseDTO::success('test-message-id-123'));

        $messageService->shouldReceive('markAsSent')
            ->once()
            ->with(1, 'test-message-id-123')
            ->andReturn(true);

        $cacheService->shouldReceive('cacheMessageId')
            ->once()
            ->with(1, 'test-message-id-123', Mockery::type('string'))
            ->andReturn(true);

        $logService->shouldReceive('info')->twice();

        $this->app->instance(WebhookServiceInterface::class, $webhookService);
        $this->app->instance(MessageServiceInterface::class, $messageService);
        $this->app->instance(CacheServiceInterface::class, $cacheService);
        $this->app->instance(LogServiceInterface::class, $logService);

        $job = new SendMessageJob(1, '+905551111111', 'Test message');
        $job->handle($webhookService, $messageService, $cacheService, $logService);

        $this->assertTrue(true);
    }

    public function test_job_marks_message_as_failed_on_error(): void
    {
        $webhookService = Mockery::mock(WebhookServiceInterface::class);
        $messageService = Mockery::mock(MessageServiceInterface::class);
        $cacheService = Mockery::mock(CacheServiceInterface::class);
        $logService = Mockery::mock(LogServiceInterface::class);

        $webhookService->shouldReceive('sendMessage')
            ->once()
            ->andReturn(WebhookResponseDTO::failure('Connection failed'));

        $messageService->shouldReceive('markAsFailed')
            ->once()
            ->with(1)
            ->andReturn(true);

        $logService->shouldReceive('info')->once();
        $logService->shouldReceive('error')->once();

        $this->app->instance(WebhookServiceInterface::class, $webhookService);
        $this->app->instance(MessageServiceInterface::class, $messageService);
        $this->app->instance(CacheServiceInterface::class, $cacheService);
        $this->app->instance(LogServiceInterface::class, $logService);

        $job = new SendMessageJob(1, '+905551111111', 'Test message');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Connection failed');

        $job->handle($webhookService, $messageService, $cacheService, $logService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
