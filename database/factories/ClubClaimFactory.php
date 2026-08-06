<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubClaim;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClubClaim>
 */
class ClubClaimFactory extends Factory
{
    protected $model = ClubClaim::class;

    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'user_id' => User::factory(),
            'message' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }
}
