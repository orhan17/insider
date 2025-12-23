<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\MessageServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MessageController extends Controller
{
    public function __construct(
        private readonly MessageServiceInterface $messageService
    ) {
    }

    public function index(): JsonResponse
    {
        $messages = $this->messageService->getSentMessages();

        return ApiResponse::success([
            'messages' => MessageResource::collection($messages),
            'count' => $messages->count(),
        ]);
    }

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $message = $this->messageService->createMessage(
            $validated['phone_number'],
            $validated['content']
        );

        return ApiResponse::success(
            new MessageResource($message),
            'Message created successfully',
            201
        );
    }
}
