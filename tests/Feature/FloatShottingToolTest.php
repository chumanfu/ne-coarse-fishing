<?php

namespace Tests\Feature;

use App\Models\PegFloatRig;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloatShottingToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_the_calculator_and_shot_guide(): void
    {
        $this->get(route('tools.float-shotting'))
            ->assertOk()
            ->assertSee('Float shotting')
            ->assertSee('Shotting pattern')
            ->assertSee('log in', false);

        $this->get(route('tools.shot-guide'))
            ->assertOk()
            ->assertSee('Shot sizes')
            ->assertSee('No.8')
            ->assertSee('0.06g');
    }

    public function test_user_can_save_a_pattern_against_a_verified_peg(): void
    {
        $user = User::factory()->create();
        $peg = $this->verifiedPeg();

        $response = $this->actingAs($user)->post(route('tools.float-shotting.store'), [
            'water_peg_id' => $peg->id,
            'float_name' => 'Preston Chianti',
            'float_size' => '4x14',
            'float_type' => 'pole',
            'float_grams' => 0.4,
            'depth' => 6,
            'depth_unit' => 'ft',
            'pattern_id' => 'bulk_droppers',
            'notes' => 'Fish came 6in off bottom',
        ]);

        $rig = PegFloatRig::query()->firstOrFail();

        $response->assertRedirect(route('tools.float-shotting', ['rig' => $rig->id]));
        $this->assertSame($peg->id, $rig->water_peg_id);
        $this->assertSame('Preston Chianti', $rig->float_name);
        $this->assertSame('bulk_droppers', $rig->pattern_id);
        $this->assertNull($rig->olivette_grams);
    }

    public function test_a_pattern_cannot_be_saved_against_an_unverified_peg(): void
    {
        $user = User::factory()->create();
        $peg = WaterPeg::factory()->for(Water::factory()->for(Venue::factory()->create(['is_approved' => true])))
            ->create(['is_verified' => false]);

        $this->actingAs($user)->post(route('tools.float-shotting.store'), [
            'water_peg_id' => $peg->id,
            'float_size' => '4x14',
            'float_type' => 'pole',
            'float_grams' => 0.4,
            'depth' => 6,
            'depth_unit' => 'ft',
            'pattern_id' => 'bulk_droppers',
        ])->assertSessionHasErrors('water_peg_id');

        $this->assertDatabaseCount('peg_float_rigs', 0);
    }

    public function test_saved_pattern_reopens_in_the_calculator_and_shows_on_the_venue(): void
    {
        $peg = $this->verifiedPeg(['description' => 'Open water in front']);
        $rig = PegFloatRig::factory()->for(User::factory())->create([
            'water_peg_id' => $peg->id,
            'float_name' => 'Drennan Crystal',
            'float_size' => '3BB',
            'float_type' => 'waggler',
            'pattern_id' => 'waggler_locking',
        ]);

        $this->get(route('tools.float-shotting', ['rig' => $rig->id]))
            ->assertOk()
            ->assertSee('Drennan Crystal');

        $this->get(route('venues.show', $peg->water->venue))
            ->assertOk()
            ->assertSee('Drennan Crystal')
            ->assertSee('Locking + droppers');
    }

    public function test_only_the_owner_can_remove_a_saved_pattern(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $rig = PegFloatRig::factory()->for($owner)->create(['water_peg_id' => $this->verifiedPeg()->id]);

        $this->actingAs($other)->delete(route('tools.float-shotting.destroy', $rig))->assertForbidden();
        $this->actingAs($owner)->delete(route('tools.float-shotting.destroy', $rig))->assertRedirect();

        $this->assertDatabaseCount('peg_float_rigs', 0);
    }

    private function verifiedPeg(array $attributes = []): WaterPeg
    {
        $venue = Venue::factory()->create(['is_approved' => true]);

        return WaterPeg::factory()
            ->for(Water::factory()->for($venue))
            ->create([...$attributes, 'is_verified' => true]);
    }
}
