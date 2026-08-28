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

    /**
     * @param  list<array{0: float, 1: float}>|null  $coordinates  GeoJSON [lng, lat] pairs
     */
    public function withLineString(?array $coordinates = null): static
    {
        $coordinates ??= [
            [-1.5800, 54.7800],
            [-1.5750, 54.7850],
            [-1.5700, 54.7900],
        ];

        return $this->state(fn () => [
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $coordinates,
            ],
            'geometry_type' => 'LineString',
        ]);
    }
}
