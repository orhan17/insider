<?php

namespace App\Services;

use App\Contracts\MessageServiceInterface;
use App\Models\Message;
use App\Repositories\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class MessageService implements MessageServiceInterface
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly int $maxContentLength = 160
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
        $this->validateMessageContent($content);
        $this->validatePhoneNumber($phoneNumber);

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

    private function validateMessageContent(string $content): void
    {
        if (mb_strlen($content) > $this->maxContentLength) {
            throw new InvalidArgumentException(
                sprintf('Message content cannot exceed %d characters', $this->maxContentLength)
            );
        }

        if (empty(trim($content))) {
            throw new InvalidArgumentException('Message content cannot be empty');
        }
    }

    private function validatePhoneNumber(string $phoneNumber): void
    {
        $validator = Validator::make(
            ['phone_number' => $phoneNumber],
            ['phone_number' => 'required|regex:/^\+[1-9]\d{1,14}$/']
        );

        if ($validator->fails()) {
            throw new InvalidArgumentException('Invalid phone number format. Expected format: +1234567890');
        }
    }
}
