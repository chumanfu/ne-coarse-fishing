<?php

namespace Tests\Feature;

use App\Models\PegFloatRig;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Support\ShotReference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloatShottingToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_the_rig_list_and_the_calculator(): void
    {
        $this->get(route('tools.rigs'))
            ->assertOk()
            ->assertSee('Rigs')
            ->assertSee('Create New Rig')
            ->assertSee('log in', false)
            ->assertDontSee('System rigs');

        $this->get(route('tools.rigs.create'))
            ->assertOk()
            ->assertSee('New rig')
            ->assertSee('Shot pattern')
            ->assertSee('centimetres or inches');

        $this->get(route('tools.float-shotting'))
            ->assertRedirect(route('tools.rigs'))
            ->assertStatus(301);

        $this->get(route('tools.shot-guide'))
            ->assertOk()
            ->assertSee('Shot sizes')
            ->assertSee('No.8')
            ->assertSee('0.06g')
            ->assertSee('0.75g')
            ->assertSee('Slider')
            ->assertDontSee('hundredths');
    }

    public function test_a_slider_rig_can_be_saved_and_shows_on_the_venue(): void
    {
        $user = User::factory()->create();
        $peg = $this->verifiedPeg();

        $this->actingAs($user)->post(route('tools.rigs.store'), $this->payload($peg, [
            'name' => 'Far bank slider',
            'float_name' => 'Drennan Loaded Slider',
            'float_size' => '4AAA',
            'float_type' => 'slider',
            'float_grams' => 3.2,
            'depth' => 15,
            'pattern_id' => 'slider',
        ]))->assertSessionHasNoErrors();

        $rig = PegFloatRig::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('slider', $rig->float_type);
        $this->assertSame('Bulk', $rig->patternLabel());
        $this->assertSame('Far bank slider', $rig->name);
        $this->assertSame($peg->id, $rig->water_peg_id);

        $this->actingAs($user)->get(route('tools.rigs'))
            ->assertOk()
            ->assertSee('Far bank slider')
            ->assertSee($peg->water->venue->name);

        $this->get(route('venues.show', $peg->water->venue))
            ->assertOk()
            ->assertSee('Far bank slider')
            ->assertSee('Drennan Loaded Slider')
            ->assertSee('Bulk · Slider · 15ft');
    }

    public function test_a_slider_olivette_rig_can_be_saved(): void
    {
        $user = User::factory()->create();
        $peg = $this->verifiedPeg();

        $this->actingAs($user)->post(route('tools.rigs.store'), $this->payload($peg, [
            'float_name' => 'Drennan Loaded Slider',
            'float_size' => '4AAA',
            'float_type' => 'slider',
            'float_grams' => 3.2,
            'depth' => 15,
            'pattern_id' => 'slider_olivette',
            'olivette_grams' => 2.5,
        ]))->assertSessionHasNoErrors();

        $rig = PegFloatRig::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('slider_olivette', $rig->pattern_id);
        $this->assertEquals(2.5, $rig->olivette_grams);
        $this->assertSame('Olivette', $rig->patternLabel());
    }

    public function test_a_loaded_waggler_can_be_saved(): void
    {
        $user = User::factory()->create();
        $peg = $this->verifiedPeg();

        $this->actingAs($user)->post(route('tools.rigs.store'), $this->payload($peg, [
            'float_name' => 'Lidsters crystal',
            'float_size' => '1+2BB 0.4+0.8 gr',
            'float_type' => 'loaded_waggler',
            'float_grams' => 0.8,
            'depth' => 6,
            'pattern_id' => 'loaded_waggler',
        ]))->assertSessionHasNoErrors();

        $rig = PegFloatRig::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('loaded_waggler', $rig->float_type);
        $this->assertSame('Bulk & droppers', $rig->patternLabel());
        $this->assertSame('Loaded waggler', $rig->floatTypeLabel());
    }

    public function test_user_can_save_a_rig_with_placed_shot_against_a_verified_peg(): void
    {
        $user = User::factory()->create();
        $peg = $this->verifiedPeg();

        $response = $this->actingAs($user)->post(route('tools.rigs.store'), $this->payload($peg, [
            'name' => 'The Standard Deep-Water Rig',
            'float_name' => 'Preston Chianti',
            'notes' => 'Fish came 6in off bottom',
            'placements' => json_encode([
                [
                    'role' => 'bulk',
                    'height_cm' => 33.02,
                    'anchor' => 'hook',
                    'distance_unit' => 'in',
                    'items' => [['size' => 'No9', 'count' => 5]],
                ],
                [
                    'role' => 'dropper',
                    'height_cm' => 15.24,
                    'anchor' => 'hook',
                    'distance_unit' => 'in',
                    'items' => [['size' => 'No10', 'count' => 1]],
                ],
            ]),
        ]));

        $rig = PegFloatRig::query()->where('user_id', $user->id)->firstOrFail();

        $response->assertRedirect(route('tools.rigs'));
        $this->assertSame($peg->id, $rig->water_peg_id);
        $this->assertTrue($rig->venues->contains('id', $peg->water->venue_id));
        $this->assertTrue($rig->pegs->contains('id', $peg->id));
        $this->assertSame('The Standard Deep-Water Rig', $rig->name);
        $this->assertSame('No9', $rig->placements[0]['items'][0]['size']);
        $this->assertSame(5, $rig->placements[0]['items'][0]['count']);
        $this->assertEquals(33.02, $rig->placements[0]['height_cm']);

        $this->get(route('tools.rigs.edit', $rig))
            ->assertOk()
            ->assertSee('The Standard Deep-Water Rig')
            ->assertSee('No9');
    }

    public function test_a_rig_can_be_saved_against_a_venue_without_a_peg(): void
    {
        $user = User::factory()->create();
        $peg = $this->verifiedPeg();

        $this->actingAs($user)->post(route('tools.rigs.store'), $this->payload($peg, [
            'name' => 'Commercial pellet rig',
            'peg_ids' => [],
            'venue_ids' => [$peg->water->venue_id],
        ]))->assertSessionHasNoErrors();

        $rig = PegFloatRig::query()->where('user_id', $user->id)->with('venues', 'pegs')->firstOrFail();

        $this->assertNull($rig->water_peg_id);
        $this->assertTrue($rig->pegs->isEmpty());
        $this->assertTrue($rig->venues->contains('id', $peg->water->venue_id));
    }

    public function test_a_rig_cannot_be_saved_against_an_unverified_peg(): void
    {
        $user = User::factory()->create();
        $peg = WaterPeg::factory()->for(Water::factory()->for(Venue::factory()->create(['is_approved' => true])))
            ->create(['is_verified' => false]);

        $this->actingAs($user)->post(route('tools.rigs.store'), $this->payload($peg))
            ->assertSessionHasErrors('peg_ids.0');

        $this->assertSame(0, PegFloatRig::query()->where('is_system', false)->count());
    }

    public function test_saved_rig_reopens_in_the_editor_and_shows_on_the_venue(): void
    {
        $peg = $this->verifiedPeg(['description' => 'Open water in front']);
        $rig = PegFloatRig::factory()->for(User::factory())->create([
            'water_peg_id' => $peg->id,
            'float_name' => 'Drennan Crystal',
            'float_size' => '3BB',
            'float_type' => 'waggler',
            'pattern_id' => 'waggler_locking',
            'name' => 'Canal waggler',
        ]);

        $this->get(route('tools.rigs.edit', $rig))
            ->assertOk()
            ->assertSee('Drennan Crystal')
            ->assertSee('Canal waggler');

        $this->get(route('tools.float-shotting', ['rig' => $rig->id]))
            ->assertRedirect(route('tools.rigs.edit', $rig))
            ->assertStatus(301);

        $this->get(route('venues.show', $peg->water->venue))
            ->assertOk()
            ->assertSee('Canal waggler')
            ->assertSee('Locking + droppers');
    }

    public function test_the_owner_can_rename_and_duplicate_a_rig(): void
    {
        $owner = User::factory()->create();
        $rig = PegFloatRig::factory()->for($owner)->create([
            'water_peg_id' => $this->verifiedPeg()->id,
            'name' => 'The Standard Deep-Water Rig',
        ]);

        $this->actingAs($owner)->patch(route('tools.rigs.rename', $rig), [
            'name' => 'Shallow pellet rig',
        ])->assertRedirect(route('tools.rigs'));

        $this->assertSame('Shallow pellet rig', $rig->fresh()->name);

        $this->actingAs($owner)->post(route('tools.rigs.duplicate', $rig), [
            'name' => 'Shallow pellet rig, peg 12',
        ])->assertRedirect(route('tools.rigs'));

        $this->assertSame(2, PegFloatRig::query()->where('user_id', $owner->id)->count());
        $this->assertTrue(PegFloatRig::query()->where('name', 'Shallow pellet rig, peg 12')->exists());
    }

    public function test_the_owner_can_add_notes_to_a_rig(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $rig = PegFloatRig::factory()->for($owner)->create([
            'water_peg_id' => $this->verifiedPeg()->id,
            'name' => 'Margin rig',
            'notes' => null,
        ]);

        $this->actingAs($owner)->get(route('tools.rigs'))
            ->assertOk()
            ->assertSee('Add notes');

        $this->actingAs($other)->patch(route('tools.rigs.notes', $rig), [
            'notes' => 'Stolen',
        ])->assertForbidden();

        $this->actingAs($owner)->patch(route('tools.rigs.notes', $rig), [
            'notes' => 'Fish came 6in off bottom',
        ])->assertRedirect(route('tools.rigs'));

        $this->assertSame('Fish came 6in off bottom', $rig->fresh()->notes);

        $this->actingAs($owner)->get(route('tools.rigs'))
            ->assertSee('Fish came 6in off bottom')
            ->assertSee('Edit notes');

        $this->actingAs($owner)->patch(route('tools.rigs.notes', $rig), [
            'notes' => '   ',
        ])->assertRedirect(route('tools.rigs'));

        $this->assertNull($rig->fresh()->notes);

        $system = PegFloatRig::query()->where('is_system', true)->firstOrFail();
        $this->actingAs($owner)->patch(route('tools.rigs.notes', $system), [
            'notes' => 'Changed',
        ])->assertForbidden();
        $this->assertNotSame('Changed', $system->fresh()->notes);
    }

    public function test_only_the_owner_can_remove_a_saved_rig(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $rig = PegFloatRig::factory()->for($owner)->create(['water_peg_id' => $this->verifiedPeg()->id]);

        $this->actingAs($other)->delete(route('tools.rigs.destroy', $rig))->assertForbidden();
        $this->actingAs($other)->patch(route('tools.rigs.rename', $rig), ['name' => 'Taken'])->assertForbidden();
        $this->actingAs($owner)->delete(route('tools.rigs.destroy', $rig))->assertRedirect();

        $this->assertSame(0, PegFloatRig::query()->where('is_system', false)->count());
    }

    public function test_system_rigs_can_be_copied_but_not_changed(): void
    {
        $user = User::factory()->create();
        $system = PegFloatRig::query()->where('system_key', 'pole-4x12-rig-1')->firstOrFail();
        $this->post(route('tools.rigs.duplicate', $system), ['name' => 'Nope'])->assertRedirect(route('login'));

        foreach (['4x10', '4x12', '4x14', '4x16', '4x18', '4x20'] as $size) {
            $this->assertSame(5, PegFloatRig::query()->where('is_system', true)->where('float_size', $size)->count());
        }

        $weights = collect(ShotReference::shots())->pluck('grams', 'size');
        foreach (PegFloatRig::query()->where('is_system', true)->get() as $rig) {
            $load = collect($rig->placements)->sum(function (array $group) use ($weights) {
                $shot = collect($group['items'])->sum(fn (array $item) => $weights[$item['size']] * $item['count']);

                return $shot + (float) ($group['olivette_grams'] ?? 0);
            });

            $this->assertGreaterThanOrEqual($rig->float_grams - 0.012, $load, $rig->name);
            $this->assertLessThanOrEqual($rig->float_grams + 0.012, $load, $rig->name);
        }

        $this->get(route('tools.rigs'))
            ->assertOk()
            ->assertDontSee('4x14 · Rig 1 – Bulk and droppers');

        $this->actingAs($user)->get(route('tools.rigs'))
            ->assertOk()
            ->assertSee('System rigs')
            ->assertSee('4x10 · Rig 1 – Bulk and droppers')
            ->assertSee('4x20 · Rig 5 – Over-depth Double Bulk')
            ->assertSee('About 0.40g');

        $this->assertSame(3, $system->placements[0]['items'][0]['count']);
        $this->assertSame('No10', $system->placements[0]['items'][0]['size']);
        $this->assertSame(0.6, PegFloatRig::query()->where('system_key', 'pole-4x18-rig-1')->firstOrFail()->olivette_grams);

        $this->actingAs($user)->get(route('tools.rigs.edit', $system))
            ->assertOk()
            ->assertSee('Duplicate this rig')
            ->assertDontSee('Save rig');

        $this->actingAs($user)->put(route('tools.rigs.update', $system), [])->assertForbidden();
        $this->actingAs($user)->patch(route('tools.rigs.rename', $system), ['name' => 'Taken'])->assertForbidden();
        $this->actingAs($user)->delete(route('tools.rigs.destroy', $system))->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin)->delete(route('tools.rigs.destroy', $system))->assertForbidden();

        $this->actingAs($user)->post(route('tools.rigs.duplicate', $system), [
            'name' => 'My 4x12 bulk',
        ])->assertRedirect(route('tools.rigs'));

        $copy = PegFloatRig::query()->where('name', 'My 4x12 bulk')->firstOrFail();
        $this->assertFalse($copy->is_system);
        $this->assertNull($copy->system_key);
        $this->assertSame($user->id, $copy->user_id);
        $this->assertSame($system->placements, $copy->placements);
        $this->assertTrue($system->fresh()->is_system);

        $this->actingAs($user)->delete(route('tools.rigs.destroy', $copy))->assertRedirect();
        $this->assertNotNull($system->fresh());
    }

    private function payload(WaterPeg $peg, array $overrides = []): array
    {
        return [
            'name' => 'The Standard Deep-Water Rig',
            'peg_ids' => [$peg->id],
            'float_name' => 'Preston Chianti',
            'float_size' => '0.4g',
            'float_type' => 'pole',
            'float_grams' => 0.4,
            'depth' => 5,
            'depth_unit' => 'ft',
            'pattern_id' => 'bulk_droppers',
            'placements' => json_encode([
                [
                    'role' => 'bulk',
                    'height_cm' => 30,
                    'anchor' => 'hook',
                    'distance_unit' => 'cm',
                    'items' => [['size' => 'No8', 'count' => 4]],
                ],
            ]),
            ...$overrides,
        ];
    }

    private function verifiedPeg(array $attributes = []): WaterPeg
    {
        $venue = Venue::factory()->create(['is_approved' => true]);

        return WaterPeg::factory()
            ->for(Water::factory()->for($venue))
            ->create([...$attributes, 'is_verified' => true]);
    }
}
