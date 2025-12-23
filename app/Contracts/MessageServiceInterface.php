<?php

namespace App\Contracts;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface MessageServiceInterface
{
    public function createMessage(string $phoneNumber, string $content): Message;
    public function getPendingMessages(int $limit): Collection;
    public function getSentMessages(): Collection;
    public function markAsSent(int $messageId, string $externalMessageId): bool;
    public function markAsFailed(int $messageId): bool;
}
