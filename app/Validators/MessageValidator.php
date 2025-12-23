<?php

declare(strict_types=1);

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

final class MessageValidator
{
    private const MAX_CONTENT_LENGTH = 160;
    private const PHONE_NUMBER_REGEX = '/^\+[1-9]\d{1,14}$/';

    public function __construct(
        private readonly int $maxContentLength = self::MAX_CONTENT_LENGTH
    ) {
    }

    public function validateContent(string $content): void
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

    public function validatePhoneNumber(string $phoneNumber): void
    {
        $validator = Validator::make(
            ['phone_number' => $phoneNumber],
            ['phone_number' => 'required|regex:' . self::PHONE_NUMBER_REGEX]
        );

        if ($validator->fails()) {
            throw new InvalidArgumentException('Invalid phone number format. Expected format: +1234567890');
        }
    }
}
