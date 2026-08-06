<?php

namespace Database\Factories;

use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageThread>
 */
class MessageThreadFactory extends Factory
{
    protected $model = MessageThread::class;

    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'subject' => fake()->sentence(4),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'source' => 'contact',
            'status' => 'open',
            'last_message_at' => now(),
            'admin_last_read_at' => null,
            'participant_last_read_at' => now(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'contact_name' => $user->name,
            'contact_email' => $user->email,
        ]);
    }
}
