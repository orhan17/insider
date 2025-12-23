<?php

namespace App\Http\Controllers\Api;

use App\Contracts\MessageServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    private MessageServiceInterface $messageService;

    public function __construct(MessageServiceInterface $messageService)
    {
        $this->messageService = $messageService;
    }

    public function index(): JsonResponse
    {
        $messages = $this->messageService->getSentMessages();

        return response()->json([
            'success' => true,
            'data' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'phone_number' => $message->phone_number,
                    'content' => $message->content,
                    'status' => $message->status,
                    'message_id' => $message->message_id,
                    'sent_at' => $message->sent_at?->toIso8601String(),
                    'created_at' => $message->created_at->toIso8601String(),
                    'updated_at' => $message->updated_at->toIso8601String(),
                ];
            }),
            'count' => $messages->count(),
        ]);
    }

    public function pending(): JsonResponse
    {
        $messages = $this->messageService->getPendingMessages(100);

        return response()->json([
            'success' => true,
            'data' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'phone_number' => $message->phone_number,
                    'content' => $message->content,
                    'status' => $message->status,
                    'message_id' => $message->message_id,
                    'sent_at' => $message->sent_at?->toIso8601String(),
                    'created_at' => $message->created_at->toIso8601String(),
                    'updated_at' => $message->updated_at->toIso8601String(),
                ];
            }),
            'count' => $messages->count(),
        ]);
    }


    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone_number' => 'required|string|regex:/^\+[1-9]\d{1,14}$/',
                'content' => 'required|string|max:160',
            ]);

            $message = $this->messageService->createMessage(
                $validated['phone_number'],
                $validated['content']
            );

            return response()->json([
                'success' => true,
                'message' => 'Message created successfully',
                'data' => [
                    'id' => $message->id,
                    'phone_number' => $message->phone_number,
                    'content' => $message->content,
                    'status' => $message->status,
                    'created_at' => $message->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
