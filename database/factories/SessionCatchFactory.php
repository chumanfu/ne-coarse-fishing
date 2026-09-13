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

    /**
     * @param  list<int|Species>|int|Species  $species
     */
    public function withSpecies(array|int|Species $species): static
    {
        $items = is_array($species) ? $species : [$species];
        $ids = collect($items)
            ->map(fn ($item) => $item instanceof Species ? $item->id : (int) $item)
            ->filter()
            ->values()
            ->all();

        return $this->state(fn () => [
            'species_id' => $ids[0] ?? Species::factory(),
        ])->afterCreating(function (SessionCatch $catch) use ($ids) {
            if ($ids !== []) {
                $catch->caughtSpecies()->sync($ids);
            }
        });
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
