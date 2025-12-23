<?php

namespace App\Providers;

use App\Contracts\MessageServiceInterface;
use App\Repositories\MessageRepository;
use App\Repositories\MessageRepositoryInterface;
use App\Services\CacheService;
use App\Services\MessageService;
use App\Services\WebhookService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);

        $this->app->singleton(MessageServiceInterface::class, MessageService::class);

        $this->app->singleton(WebhookService::class, function () {
            return new WebhookService(
                new Client([
                    'timeout' => 10,
                    'connect_timeout' => 5,
                ])
            );
        });

        $this->app->singleton(MessageService::class, function ($app) {
            /** @var int $maxLength */
            $maxLength = config('messages.max_length') ?? 160;

            return new MessageService(
                $app->make(MessageRepositoryInterface::class),
                $maxLength
            );
        });

        $this->app->singleton(CacheService::class, function () {
            return new CacheService();
        });
    }

    public function boot(): void
    {
    }
}
