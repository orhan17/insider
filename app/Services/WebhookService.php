<?php

namespace App\Services;

use App\Contracts\LogServiceInterface;
use App\Contracts\WebhookServiceInterface;
use App\DTO\WebhookResponseDTO;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WebhookService implements WebhookServiceInterface
{
    private readonly string $webhookUrl;
    private readonly string $authKey;

    public function __construct(
        private readonly Client $httpClient,
        private readonly LogServiceInterface $logService
    ) {
        $this->webhookUrl = config('services.webhook.url');
        $this->authKey = config('services.webhook.auth_key');

        if (empty($this->webhookUrl) || $this->webhookUrl === 'https://webhook.site') {
            $this->logService->warning('Webhook URL is not configured properly. Please set a valid webhook.site URL in .env file.');
        }
    }

    public function sendMessage(string $phoneNumber, string $content): WebhookResponseDTO
    {
        try {
            $response = $this->httpClient->post($this->webhookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-ins-auth-key' => $this->authKey,
                ],
                'json' => [
                    'to' => $phoneNumber,
                    'content' => $content,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 202 && isset($body['messageId'])) {
                $this->logService->info('Message sent successfully', [
                    'phone' => $phoneNumber,
                    'messageId' => $body['messageId'],
                ]);

                return WebhookResponseDTO::success($body['messageId']);
            }

            $this->logService->error('Unexpected webhook response', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            return WebhookResponseDTO::failure('Unexpected response from webhook');
        } catch (GuzzleException $e) {
            $this->logService->error('Failed to send message via webhook', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return WebhookResponseDTO::failure($e->getMessage());
        }
    }
}
