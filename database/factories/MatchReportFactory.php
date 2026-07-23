<?php

namespace Database\Factories;

use App\Models\MatchReport;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchReport>
 */
class MatchReportFactory extends Factory
{
    protected $model = MatchReport::class;

    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'water_id' => null,
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraphs(2, true),
            'published_at' => now(),
        ];
    }
}
