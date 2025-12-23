<?php

namespace App\Services;

use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Facades\Cache;

class CacheService implements CacheServiceInterface
{
    private const SENT_MESSAGE_PREFIX = 'sent_message:';
    private const TTL = 86400;

    public function cacheSentMessage(int $messageId, string $externalMessageId, string $sentAt): void
    {
        $key = $this->getKey($messageId);

        $data = [
            'message_id' => $messageId,
            'external_message_id' => $externalMessageId,
            'sent_at' => $sentAt,
            'cached_at' => now()->toIso8601String(),
        ];

        Cache::put($key, $data, self::TTL);
    }

    public function cacheMessageId(int $messageId, string $externalMessageId, string $sentAt): void
    {
        $this->cacheSentMessage($messageId, $externalMessageId, $sentAt);
    }

    public function getSentMessage(int $messageId): ?array
    {
        $key = $this->getKey($messageId);

        return Cache::get($key);
    }

    public function hasSentMessage(int $messageId): bool
    {
        $key = $this->getKey($messageId);

        return Cache::has($key);
    }

    private function getKey(int $messageId): string
    {
        return self::SENT_MESSAGE_PREFIX . $messageId;
    }
}
