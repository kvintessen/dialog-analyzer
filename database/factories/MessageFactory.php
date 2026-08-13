<?php

namespace Database\Factories;

use App\Enums\MessageSender;
use App\Models\Dialog;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dialog_id' => Dialog::factory(),
            'sender' => fake()->randomElement(MessageSender::cases()),
            'body' => fake()->sentence(),
            'sent_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function fromManager(): static
    {
        return $this->state(fn (array $attributes) => ['sender' => MessageSender::Manager]);
    }

    public function fromClient(): static
    {
        return $this->state(fn (array $attributes) => ['sender' => MessageSender::Client]);
    }
}
