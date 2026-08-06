<?php

namespace Database\Factories;

use App\Models\TackleShop;
use App\Models\TackleShopClaim;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TackleShopClaim>
 */
class TackleShopClaimFactory extends Factory
{
    protected $model = TackleShopClaim::class;

    public function definition(): array
    {
        return [
            'tackle_shop_id' => TackleShop::factory(),
            'user_id' => User::factory(),
            'message' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }
}
