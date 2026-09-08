<?php

namespace Database\Factories;

use App\Models\FishingSession;
use App\Models\SessionCatch;
use App\Models\Species;
use App\Support\Weight;
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
            'weight_g' => fake()->numberBetween(230, 13600),
            'entry_type' => SessionCatch::TYPE_INDIVIDUAL,
            'bait' => fake()->randomElement(['Boilie', 'Pellet', 'Maggot', 'Corn']),
            'quantity' => 1,
            'is_notable' => false,
            'entered_unit' => Weight::UNIT_LB_OZ,
        ];
    }

    /** A species total for the session rather than a single fish. */
    public function bag(int $quantity = 20): static
    {
        return $this->state(fn () => [
            'entry_type' => SessionCatch::TYPE_BAG,
            'quantity' => $quantity,
            'weight_g' => fake()->numberBetween(2000, 30000),
        ]);
    }

    /** A specimen singled out alongside a bag total. */
    public function notable(): static
    {
        return $this->state(fn () => [
            'entry_type' => SessionCatch::TYPE_INDIVIDUAL,
            'quantity' => 1,
            'is_notable' => true,
        ]);
    }
}
