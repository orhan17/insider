<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface
{
    public function findById(int $id): ?Message;

    public function findPendingMessages(int $limit): Collection;

    public function findSentMessages(): Collection;

    public function create(array $data): Message;

    public function update(int $id, array $data): bool;

    public function markAsSent(int $id, string $messageId): bool;

    public function markAsFailed(int $id): bool;
}
