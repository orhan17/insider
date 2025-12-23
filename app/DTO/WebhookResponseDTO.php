<?php

declare(strict_types=1);

namespace App\DTO;

final class WebhookResponseDTO implements \JsonSerializable
{
    public bool $success;
    public ?string $messageId;
    public ?string $error;

    private function __construct(
        bool $success,
        ?string $messageId = null,
        ?string $error = null
    ) {
        $this->success = $success;
        $this->messageId = $messageId;
        $this->error = $error;
    }

    public static function success(string $messageId): self
    {
        return new self(
            success: true,
            messageId: $messageId
        );
    }

    public static function failure(string $error): self
    {
        return new self(
            success: false,
            error: $error
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'messageId' => $this->messageId,
            'error' => $this->error,
        ];
    }
}
