<?php

namespace Database\Factories;

use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Species>
 */
class SpeciesFactory extends Factory
{
    protected $model = Species::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Carp', 'Tench', 'Bream', 'Roach', 'Pike', 'Perch', 'Chub', 'Barbel']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(array_keys(Species::TYPES)),
            'habitats' => fake()->randomElements(array_keys(Species::HABITATS), fake()->numberBetween(1, 3)),
        ];
    }
}
