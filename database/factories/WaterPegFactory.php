<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Water;
use App\Models\WaterPeg;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaterPeg>
 */
class WaterPegFactory extends Factory
{
    protected $model = WaterPeg::class;

    public function definition(): array
    {
        return [
            'water_id' => Water::factory(),
            'created_by' => User::factory(),
            'name' => fake()->optional()->randomElement(['Island', 'Car park', 'Dam wall', 'Point']),
            'number' => (string) fake()->numberBetween(1, 40),
            'latitude' => null,
            'longitude' => null,
            'map_x' => fake()->randomFloat(2, 10, 90),
            'map_y' => fake()->randomFloat(2, 10, 90),
            'is_verified' => true,
            'verified_at' => now(),
            'sort_order' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ]);
    }
}
