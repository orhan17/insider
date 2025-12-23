<?php

namespace Tests\Feature;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessPendingMessagesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_pending_messages_to_queue(): void
    {
        Queue::fake();

        Message::factory()->count(5)->create(['status' => Message::STATUS_PENDING]);
        Message::factory()->count(2)->create(['status' => Message::STATUS_SENT]);

        $this->artisan('messages:process')
            ->expectsOutput('Starting to process pending messages...')
            ->expectsOutput('Found 5 pending messages')
            ->assertSuccessful();

        Queue::assertPushed(SendMessageJob::class, 5);
    }

    public function test_command_respects_limit_option(): void
    {
        Queue::fake();

        Message::factory()->count(10)->create(['status' => Message::STATUS_PENDING]);

        $this->artisan('messages:process', ['--limit' => 3])
            ->expectsOutput('Found 3 pending messages')
            ->assertSuccessful();

        Queue::assertPushed(SendMessageJob::class, 3);
    }

    public function test_command_handles_no_pending_messages(): void
    {
        Queue::fake();

        Message::factory()->count(5)->create(['status' => Message::STATUS_SENT]);

        $this->artisan('messages:process')
            ->expectsOutput('No pending messages found.')
            ->assertSuccessful();

        Queue::assertNothingPushed();
    }

    public function test_command_shows_rate_limit_info(): void
    {
        Queue::fake();

        Message::factory()->count(2)->create(['status' => Message::STATUS_PENDING]);

        $this->artisan('messages:process')
            ->expectsOutput('Rate limit: 2 messages every 5 seconds')
            ->assertSuccessful();
    }
}
