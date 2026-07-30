<?php

namespace Database\Factories;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
{
    protected $model = Club::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Angling Club';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'url' => 'https://example.com/'.Str::slug($name),
            'overview' => fake()->sentence(14),
            'town' => fake()->city(),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'phone' => '01'.fake()->numerify('## ######'),
            'is_featured' => false,
            'sort_order' => 100,
            'is_published' => true,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
