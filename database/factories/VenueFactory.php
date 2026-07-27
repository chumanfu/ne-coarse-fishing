<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Lakes';

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'overview' => fake()->paragraphs(2, true),
            'latitude' => fake()->latitude(54.5, 55.5),
            'longitude' => fake()->longitude(-2.2, -1.2),
            'address' => fake()->address(),
            'url' => fake()->optional(0.6)->url(),
            'directions' => fake()->sentence(),
            'day_ticket_info' => 'Day tickets on the bank.',
            'membership_info' => null,
            'ticket_type' => fake()->randomElement(['day_ticket', 'club', 'syndicate', 'mixed']),
            'opening_times' => 'Dawn till dusk',
            'season_info' => 'Open year-round',
            'tactics_guide' => fake()->paragraph(),
            'is_complex' => false,
            'is_approved' => true,
            'manager_verified' => false,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['is_approved' => false]);
    }
}
