<?php

namespace Database\Factories;

use App\Models\Venue;
use App\Models\Water;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Water>
 */
class WaterFactory extends Factory
{
    protected $model = Water::class;

    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'name' => fake()->randomElement(['Match Lake', 'Specimen Lake', 'Canal Stretch', 'Pond 1']),
            'description' => fake()->sentence(),
            'peg_count' => fake()->numberBetween(8, 40),
            'depth_info' => fake()->randomElement(['3–5ft', '4–8ft', '6–12ft']),
            'sort_order' => 0,
        ];
    }
}
