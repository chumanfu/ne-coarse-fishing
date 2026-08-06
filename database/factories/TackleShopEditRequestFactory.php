<?php

namespace Database\Factories;

use App\Models\TackleShop;
use App\Models\TackleShopEditRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TackleShopEditRequest>
 */
class TackleShopEditRequestFactory extends Factory
{
    protected $model = TackleShopEditRequest::class;

    public function definition(): array
    {
        return [
            'tackle_shop_id' => TackleShop::factory(),
            'user_id' => User::factory(),
            'message' => fake()->optional()->sentence(),
            'proposed_data' => [
                'name' => fake()->company().' Tackle',
                'url' => fake()->url(),
                'location_type' => 'local',
            ],
            'status' => 'pending',
        ];
    }
}
