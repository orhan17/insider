<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\CacheServiceInterface;
use App\Contracts\LogServiceInterface;
use App\Contracts\MessageServiceInterface;
use App\Contracts\WebhookServiceInterface;
use App\Repositories\MessageRepository;
use App\Repositories\MessageRepositoryInterface;
use App\Services\CacheService;
use App\Services\LogService;
use App\Services\MessageService;
use App\Services\WebhookService;
use App\Validators\MessageValidator;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);

        $this->app->singleton(MessageValidator::class, function () {
            $maxLength = config('messages.max_length');

            return new MessageValidator(is_int($maxLength) ? $maxLength : 160);
        });

        $this->app->singleton(MessageServiceInterface::class, function ($app) {
            return new MessageService(
                $app->make(MessageRepositoryInterface::class),
                $app->make(MessageValidator::class)
            );
        });

        $this->app->singleton(LogServiceInterface::class, function () {
            return new LogService();
        });

        $this->app->singleton(WebhookServiceInterface::class, function ($app) {
            return new WebhookService(
                new Client([
                    'timeout' => 10,
                    'connect_timeout' => 5,
                ]),
                $app->make(LogServiceInterface::class)
            );
        });

        $this->app->singleton(CacheServiceInterface::class, function () {
            return new CacheService();
        });
    }

    public function boot(): void
    {
    }
}
