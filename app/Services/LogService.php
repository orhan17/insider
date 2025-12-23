<?php

namespace App\Services;

use App\Contracts\LogServiceInterface;
use Illuminate\Support\Facades\Log;

class LogService implements LogServiceInterface
{
    public function info(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        Log::debug($message, $context);
    }
}
