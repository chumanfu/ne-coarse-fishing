<?php

namespace Database\Factories;

use App\Models\SiteAnnouncement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteAnnouncement>
 */
class SiteAnnouncementFactory extends Factory
{
    protected $model = SiteAnnouncement::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(6),
            'body' => fake()->paragraph(),
            'level' => fake()->randomElement(['info', 'warning', 'maintenance']),
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(2),
            'is_active' => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDay(),
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
