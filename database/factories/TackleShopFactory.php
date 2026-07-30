<?php

namespace Database\Factories;

use App\Models\TackleShop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TackleShop>
 */
class TackleShopFactory extends Factory
{
    protected $model = TackleShop::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Tackle';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'url' => 'https://example.com/'.Str::slug($name),
            'overview' => fake()->sentence(12),
            'town' => fake()->city(),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'phone' => '01'.fake()->numerify('## ######'),
            'location_type' => fake()->randomElement(['local', 'online', 'hybrid']),
            'is_featured' => false,
            'sort_order' => 100,
            'is_published' => true,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function online(): static
    {
        return $this->state(fn () => [
            'location_type' => 'online',
            'town' => null,
            'address' => null,
            'phone' => null,
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
