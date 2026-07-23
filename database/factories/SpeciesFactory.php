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
        ];
    }
}
