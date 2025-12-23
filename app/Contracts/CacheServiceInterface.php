<?php

namespace App\Contracts;

interface CacheServiceInterface
{
    /**
     * Cache sent message information
     */
    public function cacheSentMessage(int $messageId, string $externalMessageId, string $sentAt): void;

    /**
     * Cache message ID and sent timestamp
     */
    public function cacheMessageId(int $messageId, string $externalMessageId, string $sentAt): void;

    /**
     * Get cached sent message
     *
     * @return array|null
     */
    public function getSentMessage(int $messageId): ?array;

    /**
     * Check if message is cached
     */
    public function hasSentMessage(int $messageId): bool;
}
