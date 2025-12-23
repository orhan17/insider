<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MessageServiceInterface;
use App\Models\Message;
use App\Repositories\MessageRepositoryInterface;
use App\Validators\MessageValidator;
use Illuminate\Database\Eloquent\Collection;

final class MessageService implements MessageServiceInterface
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly MessageValidator $validator
    ) {
    }

    public function getPendingMessages(int $limit): Collection
    {
        return $this->messageRepository->findPendingMessages($limit);
    }

    public function getSentMessages(): Collection
    {
        return $this->messageRepository->findSentMessages();
    }

    public function createMessage(string $phoneNumber, string $content): Message
    {
        $this->validator->validatePhoneNumber($phoneNumber);
        $this->validator->validateContent($content);

        return $this->messageRepository->create([
            'phone_number' => $phoneNumber,
            'content' => $content,
            'status' => Message::STATUS_PENDING,
        ]);
    }

    public function markAsSent(int $messageId, string $externalMessageId): bool
    {
        return $this->messageRepository->markAsSent($messageId, $externalMessageId);
    }

    public function markAsFailed(int $messageId): bool
    {
        return $this->messageRepository->markAsFailed($messageId);
    }
}
