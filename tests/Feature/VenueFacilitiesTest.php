<?php

namespace Tests\Feature;

use App\Livewire\VenueWizard;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VenueFacilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_wizard_persists_venue_facilities(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('angler');
        $user->assignRole('angler');

        Livewire::actingAs($user)
            ->test(VenueWizard::class)
            ->set('step', 5)
            ->set('locationSet', true)
            ->set('latitude', 54.78)
            ->set('longitude', -1.57)
            ->set('name', 'Facilities Test Lakes')
            ->set('overview', 'Venue with facilities.')
            ->set('address', 'Durham')
            ->set('ticket_type', 'day_ticket')
            ->set('waters.0.name', 'Main lake')
            ->set('facilities', ['wifi', 'toilets', 'car_park'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $venue = Venue::query()->where('name', 'Facilities Test Lakes')->first();

        $this->assertNotNull($venue);
        $this->assertSame(['wifi', 'toilets', 'car_park'], $venue->facilities);
    }

    public function test_venue_show_displays_all_facilities_with_unavailable_greyed_out(): void
    {
        $venue = Venue::factory()->create([
            'is_approved' => true,
            'facilities' => ['wifi', 'toilets', 'car_park'],
        ]);

        $response = $this->get(route('venues.show', $venue));

        $response->assertOk()
            ->assertSee('Facilities', false)
            ->assertSee('WiFi', false)
            ->assertSee('Toilets', false)
            ->assertSee('Car park', false)
            ->assertSee('Camping', false)
            ->assertSee('text-slate-400', false);
    }
}
