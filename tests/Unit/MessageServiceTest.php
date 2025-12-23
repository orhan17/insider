<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    private MessageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MessageService::class);
    }

    public function test_can_create_message_with_valid_data(): void
    {
        $message = $this->service->createMessage('+905551111111', 'Test message');

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('+905551111111', $message->phone_number);
        $this->assertEquals('Test message', $message->content);
    }

    public function test_throws_exception_when_content_exceeds_max_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message content cannot exceed 160 characters');

        $longContent = str_repeat('a', 161);
        $this->service->createMessage('+905551111111', $longContent);
    }

    public function test_throws_exception_when_content_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message content cannot be empty');

        $this->service->createMessage('+905551111111', '   ');
    }

    public function test_throws_exception_when_phone_number_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number format');

        $this->service->createMessage('invalid-phone', 'Test message');
    }

    public function test_can_get_pending_messages(): void
    {
        Message::factory()->count(5)->create(['status' => Message::STATUS_PENDING]);

        $messages = $this->service->getPendingMessages(10);

        $this->assertCount(5, $messages);
    }

    public function test_can_get_sent_messages(): void
    {
        Message::factory()->count(3)->create(['status' => Message::STATUS_SENT]);

        $messages = $this->service->getSentMessages();

        $this->assertCount(3, $messages);
    }
}
