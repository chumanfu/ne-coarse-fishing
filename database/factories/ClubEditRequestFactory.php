<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubEditRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClubEditRequest>
 */
class ClubEditRequestFactory extends Factory
{
    protected $model = ClubEditRequest::class;

    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'user_id' => User::factory(),
            'message' => fake()->optional()->sentence(),
            'proposed_data' => [
                'name' => fake()->company().' Angling Club',
                'town' => fake()->city(),
            ],
            'status' => 'pending',
        ];
    }
}
