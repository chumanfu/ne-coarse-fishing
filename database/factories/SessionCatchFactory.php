<?php

namespace Database\Factories;

use App\Models\FishingSession;
use App\Models\SessionCatch;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionCatch>
 */
class SessionCatchFactory extends Factory
{
    protected $model = SessionCatch::class;

    public function definition(): array
    {
        return [
            'fishing_session_id' => FishingSession::factory(),
            'species_id' => Species::factory(),
            'weight_lb' => fake()->randomFloat(2, 0.5, 30),
            'bait' => fake()->randomElement(['Boilie', 'Pellet', 'Maggot', 'Corn']),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
