<?php

namespace Database\Factories;

use App\Models\TackleReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TackleReview>
 */
class TackleReviewFactory extends Factory
{
    protected $model = TackleReview::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'brand' => fake()->optional()->company(),
            'rating' => fake()->numberBetween(0, 5),
            'body' => fake()->paragraphs(2, true),
            'purchase_url' => fake()->optional()->url(),
            'is_published' => true,
            'featured_on_home' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
            'featured_on_home' => true,
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => [
            'is_published' => false,
            'featured_on_home' => false,
        ]);
    }
}
