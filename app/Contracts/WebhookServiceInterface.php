<?php

namespace App\Contracts;

use App\DTO\WebhookResponseDTO;

interface WebhookServiceInterface
{
    /**
     * Send message via webhook
     *
     * @param string $phoneNumber
     * @param string $content
     * @return WebhookResponseDTO
     */
    public function sendMessage(string $phoneNumber, string $content): WebhookResponseDTO;
}
