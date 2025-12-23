<?php

namespace Tests\Unit;

use App\Contracts\CacheServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private CacheServiceInterface $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = app(CacheServiceInterface::class);
    }

    public function test_can_cache_sent_message(): void
    {
        $messageId = 1;
        $externalMessageId = 'test-message-id-123';
        $sentAt = now()->toIso8601String();

        $this->cacheService->cacheSentMessage($messageId, $externalMessageId, $sentAt);

        $this->assertTrue($this->cacheService->hasSentMessage($messageId));
    }

    public function test_can_retrieve_cached_message(): void
    {
        $messageId = 1;
        $externalMessageId = 'test-message-id-123';
        $sentAt = now()->toIso8601String();

        $this->cacheService->cacheSentMessage($messageId, $externalMessageId, $sentAt);
        $cached = $this->cacheService->getSentMessage($messageId);

        $this->assertNotNull($cached);
        $this->assertEquals($messageId, $cached['message_id']);
        $this->assertEquals($externalMessageId, $cached['external_message_id']);
        $this->assertEquals($sentAt, $cached['sent_at']);
    }

    public function test_returns_null_for_non_existent_message(): void
    {
        $cached = $this->cacheService->getSentMessage(999);

        $this->assertNull($cached);
        $this->assertFalse($this->cacheService->hasSentMessage(999));
    }
}
