<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    private readonly string $webhookUrl;
    private readonly string $authKey;

    public function __construct(
        private readonly Client $httpClient
    ) {
        $this->webhookUrl = config('services.webhook.url');
        $this->authKey = config('services.webhook.auth_key');
    }

    public function sendMessage(string $phoneNumber, string $content): array
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
                Log::info('Message sent successfully', [
                    'phone' => $phoneNumber,
                    'messageId' => $body['messageId'],
                ]);

                return [
                    'success' => true,
                    'messageId' => $body['messageId'],
                    'error' => null,
                ];
            }

            Log::error('Unexpected webhook response', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            return [
                'success' => false,
                'messageId' => null,
                'error' => 'Unexpected response from webhook',
            ];
        } catch (GuzzleException $e) {
            Log::error('Failed to send message via webhook', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'messageId' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
