<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Repositories\MessageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MessageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MessageRepository(new Message());
    }

    public function test_can_create_message(): void
    {
        $data = [
            'phone_number' => '+905551111111',
            'content' => 'Test message',
            'status' => Message::STATUS_PENDING,
        ];

        $message = $this->repository->create($data);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals($data['phone_number'], $message->phone_number);
        $this->assertEquals($data['content'], $message->content);
        $this->assertEquals(Message::STATUS_PENDING, $message->status);
    }

    public function test_can_find_pending_messages(): void
    {
        Message::factory()->count(5)->create(['status' => Message::STATUS_PENDING]);
        Message::factory()->count(3)->create(['status' => Message::STATUS_SENT]);

        $pendingMessages = $this->repository->findPendingMessages(10);

        $this->assertCount(5, $pendingMessages);
        $pendingMessages->each(function ($message) {
            $this->assertEquals(Message::STATUS_PENDING, $message->status);
        });
    }

    public function test_can_find_sent_messages(): void
    {
        Message::factory()->count(3)->create(['status' => Message::STATUS_PENDING]);
        Message::factory()->count(5)->create(['status' => Message::STATUS_SENT]);

        $sentMessages = $this->repository->findSentMessages();

        $this->assertCount(5, $sentMessages);
        $sentMessages->each(function ($message) {
            $this->assertEquals(Message::STATUS_SENT, $message->status);
        });
    }

    public function test_can_mark_message_as_sent(): void
    {
        $message = Message::factory()->create(['status' => Message::STATUS_PENDING]);
        $messageId = 'test-message-id-123';

        $result = $this->repository->markAsSent($message->id, $messageId);

        $this->assertTrue($result);
        $message->refresh();
        $this->assertEquals(Message::STATUS_SENT, $message->status);
        $this->assertEquals($messageId, $message->message_id);
        $this->assertNotNull($message->sent_at);
    }

    public function test_can_mark_message_as_failed(): void
    {
        $message = Message::factory()->create(['status' => Message::STATUS_PENDING]);

        $result = $this->repository->markAsFailed($message->id);

        $this->assertTrue($result);
        $message->refresh();
        $this->assertEquals(Message::STATUS_FAILED, $message->status);
    }
}
