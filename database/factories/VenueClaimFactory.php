<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Venue;
use App\Models\VenueClaim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VenueClaim>
 */
class VenueClaimFactory extends Factory
{
    protected $model = VenueClaim::class;

    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'user_id' => User::factory(),
            'message' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
