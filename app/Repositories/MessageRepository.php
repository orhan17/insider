<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function __construct(
        private readonly Message $model
    ) {
    }

    public function findById(int $id): ?Message
    {
        return $this->model->find($id);
    }

    /**
     * @return Collection<int, Message>
     */
    public function findPendingMessages(int $limit): Collection
    {
        /** @var Collection<int, Message> */
        return $this->model
            ->query()
            ->where('status', Message::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Message>
     */
    public function findSentMessages(): Collection
    {
        /** @var Collection<int, Message> */
        return $this->model
            ->query()
            ->where('status', Message::STATUS_SENT)
            ->orderBy('sent_at', 'desc')
            ->get();
    }

    public function create(array $data): Message
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $message = $this->findById($id);

        if (!$message) {
            return false;
        }

        return $message->update($data);
    }

    public function markAsSent(int $id, string $messageId): bool
    {
        $message = $this->findById($id);

        if (!$message) {
            return false;
        }

        return $message->update([
            'status' => Message::STATUS_SENT,
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(int $id): bool
    {
        $message = $this->findById($id);

        if (!$message) {
            return false;
        }

        return $message->update([
            'status' => Message::STATUS_FAILED,
        ]);
    }
}
