<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'message_thread_id' => MessageThread::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_from_admin' => false,
        ];
    }

    public function fromAdmin(): static
    {
        return $this->state(fn () => [
            'is_from_admin' => true,
        ]);
    }
}
