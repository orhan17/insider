<?php

namespace Tests\Feature;

use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_sent_messages(): void
    {
        Message::factory()->count(3)->create([
            'status' => Message::STATUS_SENT,
            'message_id' => 'test-message-id',
        ]);

        $response = $this->getJson('/api/v1/messages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'messages' => [
                        '*' => [
                            'id',
                            'phone_number',
                            'content',
                            'status',
                            'message_id',
                            'sent_at',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'count',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 3,
                ],
            ]);
    }

    public function test_sent_messages_only_returns_sent_status(): void
    {
        Message::factory()->count(2)->create(['status' => Message::STATUS_PENDING]);
        Message::factory()->count(3)->create(['status' => Message::STATUS_SENT]);

        $response = $this->getJson('/api/v1/messages');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 3,
                ],
            ]);

        $data = $response->json('data.messages');
        foreach ($data as $message) {
            $this->assertEquals(Message::STATUS_SENT, $message['status']);
        }
    }

    public function test_returns_empty_array_when_no_sent_messages(): void
    {
        Message::factory()->count(5)->create(['status' => Message::STATUS_PENDING]);

        $response = $this->getJson('/api/v1/messages');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 0,
                    'messages' => [],
                ],
            ]);
    }

    public function test_can_create_message(): void
    {
        $payload = [
            'phone_number' => '+905551111111',
            'content' => 'Test message content',
        ];

        $response = $this->postJson('/api/v1/messages', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'phone_number',
                    'content',
                    'status',
                    'created_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Message created successfully',
            ]);

        $this->assertDatabaseHas('messages', [
            'phone_number' => '+905551111111',
            'content' => 'Test message content',
            'status' => Message::STATUS_PENDING,
        ]);
    }

    public function test_create_message_validates_phone_number(): void
    {
        $payload = [
            'phone_number' => 'invalid-phone',
            'content' => 'Test message',
        ];

        $response = $this->postJson('/api/v1/messages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    public function test_create_message_validates_content_length(): void
    {
        $payload = [
            'phone_number' => '+905551111111',
            'content' => str_repeat('a', 161), // 161 characters (exceeds 160 limit)
        ];

        $response = $this->postJson('/api/v1/messages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_create_message_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/messages', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number', 'content']);
    }
}
