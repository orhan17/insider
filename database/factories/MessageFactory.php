<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'phone_number' => '+905' . fake()->numerify('#########'),
            'content' => fake()->text(100),
            'status' => Message::STATUS_PENDING,
            'message_id' => null,
            'sent_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Message::STATUS_PENDING,
            'message_id' => null,
            'sent_at' => null,
        ]);
    }
}
