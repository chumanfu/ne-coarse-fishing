<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['announcement', 'stocking']),
            'title' => fake()->sentence(5),
            'body' => fake()->paragraph(),
            'published_at' => now(),
            'ends_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'published_at' => now()->subDays(3),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'published_at' => now()->addDay(),
            'ends_at' => now()->addDays(3),
        ]);
    }
}
