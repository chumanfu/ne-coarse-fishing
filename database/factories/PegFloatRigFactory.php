<?php

namespace Database\Factories;

use App\Models\PegFloatRig;
use App\Models\User;
use App\Models\WaterPeg;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PegFloatRig>
 */
class PegFloatRigFactory extends Factory
{
    protected $model = PegFloatRig::class;

    public function definition(): array
    {
        return [
            'water_peg_id' => WaterPeg::factory(),
            'user_id' => User::factory(),
            'float_name' => 'Preston Chianti',
            'float_size' => '4x14',
            'float_type' => 'pole',
            'float_grams' => 0.4,
            'depth' => 6,
            'depth_unit' => 'ft',
            'pattern_id' => 'bulk_droppers',
            'olivette_grams' => null,
            'notes' => null,
            'name' => null,
            'placements' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PegFloatRig $rig): void {
            if (! $rig->water_peg_id) {
                return;
            }

            $rig->pegs()->syncWithoutDetaching([$rig->water_peg_id]);

            $venueId = WaterPeg::query()->with('water')->find($rig->water_peg_id)?->water?->venue_id;
            if ($venueId) {
                $rig->venues()->syncWithoutDetaching([$venueId]);
            }
        });
    }
}
