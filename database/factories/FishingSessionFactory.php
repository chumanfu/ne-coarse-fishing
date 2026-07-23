<?php

namespace Database\Factories;

use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FishingSession>
 */
class FishingSessionFactory extends Factory
{
    protected $model = FishingSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'venue_id' => Venue::factory(),
            'water_id' => null,
            'fished_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'duration_hours' => fake()->numberBetween(3, 12),
            'weather' => fake()->randomElement(['Sunny', 'Overcast', 'Light rain', 'Windy']),
            'peg_number' => (string) fake()->numberBetween(1, 30),
            'commentary' => fake()->paragraph(),
        ];
    }
}
