<?php

namespace Tests\Feature;

use App\Livewire\VenueWizard;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RiverStretchGeometryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_wizard_saves_stretch_geometry_on_primary_water(): void
    {
        $user = $this->makeAngler();

        $geometry = [
            'type' => 'LineString',
            'coordinates' => [
                [-1.5800, 54.7800],
                [-1.5750, 54.7850],
                [-1.5700, 54.7900],
            ],
        ];

        Livewire::actingAs($user)
            ->test(VenueWizard::class)
            ->set('step', 5)
            ->set('locationSet', true)
            ->set('locationMode', 'stretch')
            ->set('latitude', 54.781)
            ->set('longitude', -1.579)
            ->set('stretchGeometry', $geometry)
            ->set('name', 'Wear Stretch Venue')
            ->set('overview', 'A river beat with access pin.')
            ->set('address', 'Durham')
            ->set('ticket_type', 'day_ticket')
            ->set('waters.0.name', 'Main stretch')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('venues.show', Venue::query()->where('name', 'Wear Stretch Venue')->first()));

        $venue = Venue::query()->where('name', 'Wear Stretch Venue')->first();
        $this->assertNotNull($venue);
        $this->assertSame(54.781, (float) $venue->latitude);
        $this->assertSame(-1.579, (float) $venue->longitude);

        $water = $venue->waters()->first();
        $this->assertNotNull($water);
        $this->assertTrue($water->hasGeometry());
        $this->assertSame('LineString', $water->geometry_type);
        $this->assertSame('LineString', $water->geometry['type']);
        $this->assertCount(3, $water->geometry['coordinates']);
    }

    public function test_point_mode_leaves_water_geometry_null(): void
    {
        $user = $this->makeAngler();

        Livewire::actingAs($user)
            ->test(VenueWizard::class)
            ->set('step', 5)
            ->set('locationSet', true)
            ->set('locationMode', 'point')
            ->set('latitude', 54.78)
            ->set('longitude', -1.57)
            ->set('name', 'Point Only Pond')
            ->set('overview', 'Still a point venue.')
            ->set('address', 'Newcastle')
            ->set('ticket_type', 'day_ticket')
            ->set('waters.0.name', 'Pond')
            ->call('save')
            ->assertHasNoErrors();

        $water = Venue::query()->where('name', 'Point Only Pond')->first()?->waters()->first();
        $this->assertNotNull($water);
        $this->assertNull($water->geometry);
        $this->assertNull($water->geometry_type);
        $this->assertFalse($water->hasGeometry());
    }

    public function test_stretch_mode_requires_geometry_before_continuing(): void
    {
        $user = $this->makeAngler();

        Livewire::actingAs($user)
            ->test(VenueWizard::class)
            ->set('locationSet', true)
            ->set('locationMode', 'stretch')
            ->set('latitude', 54.78)
            ->set('longitude', -1.57)
            ->set('stretchGeometry', null)
            ->call('nextStep')
            ->assertHasErrors(['stretchGeometry']);
    }

    public function test_home_and_region_map_payloads_include_water_geometries(): void
    {
        $venue = Venue::factory()->create([
            'name' => 'Mapped River Beat',
            'is_approved' => true,
            'latitude' => 54.8,
            'longitude' => -1.6,
        ]);

        Water::factory()->for($venue)->withLineString([
            [-1.605, 54.795],
            [-1.600, 54.800],
            [-1.595, 54.805],
        ])->create(['name' => 'Beat A']);

        $home = $this->get(route('home'))->assertOk();
        $home->assertSee('Mapped River Beat', false);
        $home->assertSee('LineString', false);
        $home->assertSee('-1.605', false);

        $map = $this->get(route('map.index'))->assertOk();
        $map->assertSee('Mapped River Beat', false);
        $map->assertSee('LineString', false);
        $map->assertSee('-1.595', false);
    }

    public function test_venue_show_includes_water_geometry_for_map(): void
    {
        $venue = Venue::factory()->create([
            'name' => 'Show Stretch Venue',
            'is_approved' => true,
            'latitude' => 54.77,
            'longitude' => -1.58,
        ]);

        Water::factory()->for($venue)->withLineString([
            [-1.582, 54.768],
            [-1.578, 54.772],
        ])->create(['name' => 'River beat']);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Show Stretch Venue', false)
            ->assertSee('LineString', false)
            ->assertSee('-1.582', false)
            ->assertSee('fitBounds', false);
    }

    public function test_switching_to_point_mode_clears_existing_stretch_on_save(): void
    {
        Role::findOrCreate('fishery_manager');
        $user = User::factory()->create();
        $user->assignRole('fishery_manager');

        $venue = Venue::factory()->create([
            'user_id' => $user->id,
            'manager_id' => $user->id,
            'name' => 'Was A Stretch',
            'is_approved' => true,
        ]);
        Water::factory()->for($venue)->withLineString()->create(['name' => 'Old beat']);

        Livewire::actingAs($user)
            ->test(VenueWizard::class, ['venue' => $venue])
            ->assertSet('locationMode', 'stretch')
            ->set('step', 5)
            ->call('setLocationMode', 'point')
            ->assertSet('stretchGeometry', null)
            ->set('waters.0.name', 'Old beat')
            ->call('save')
            ->assertHasNoErrors();

        $water = $venue->fresh()->waters()->first();
        $this->assertNull($water->geometry);
        $this->assertNull($water->geometry_type);
    }

    private function makeAngler(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('angler');
        $user->assignRole('angler');

        return $user;
    }
}
