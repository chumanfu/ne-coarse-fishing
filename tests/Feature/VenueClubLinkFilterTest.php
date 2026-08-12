<?php

namespace Tests\Feature;

use App\Filament\Resources\Venues\Pages\ListVenues;
use App\Models\Club;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VenueClubLinkFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_venues_index_can_filter_independent_venues(): void
    {
        Venue::factory()->create([
            'name' => 'ZZZ Independent Mere',
            'is_approved' => true,
        ]);

        $clubVenue = Venue::factory()->create([
            'name' => 'ZZZ Club Waters Lake',
            'is_approved' => true,
        ]);
        $clubVenue->clubs()->attach(Club::factory()->create());

        $this->get(route('venues.index', [
            'club_link' => 'independent',
            'q' => 'ZZZ',
        ]))
            ->assertOk()
            ->assertSee('Club link')
            ->assertSee('ZZZ Independent Mere')
            ->assertDontSee('ZZZ Club Waters Lake');
    }

    public function test_venues_index_can_filter_club_waters(): void
    {
        Venue::factory()->create([
            'name' => 'ZZZ Independent Mere',
            'is_approved' => true,
        ]);

        $clubVenue = Venue::factory()->create([
            'name' => 'ZZZ Club Waters Lake',
            'is_approved' => true,
        ]);
        $clubVenue->clubs()->attach(Club::factory()->create());

        $this->get(route('venues.index', [
            'club_link' => 'club',
            'q' => 'ZZZ',
        ]))
            ->assertOk()
            ->assertSee('ZZZ Club Waters Lake')
            ->assertDontSee('ZZZ Independent Mere');
    }

    public function test_venues_index_ignores_unknown_club_link_values(): void
    {
        Venue::factory()->create([
            'name' => 'ZZZ Independent Mere',
            'is_approved' => true,
        ]);

        $clubVenue = Venue::factory()->create([
            'name' => 'ZZZ Club Waters Lake',
            'is_approved' => true,
        ]);
        $clubVenue->clubs()->attach(Club::factory()->create());

        $this->get(route('venues.index', [
            'club_link' => 'not-a-real-filter',
            'q' => 'ZZZ',
        ]))
            ->assertOk()
            ->assertSee('ZZZ Independent Mere')
            ->assertSee('ZZZ Club Waters Lake');
    }

    public function test_filament_venues_table_can_filter_by_club_link(): void
    {
        Role::findOrCreate('super_admin');

        $admin = User::factory()->create();
        $admin->syncRoles(['super_admin']);

        $independent = Venue::factory()->create([
            'name' => 'ZZZ Independent Mere',
            'is_approved' => true,
        ]);

        $clubVenue = Venue::factory()->create([
            'name' => 'ZZZ Club Waters Lake',
            'is_approved' => true,
        ]);
        $clubVenue->clubs()->attach(Club::factory()->create());

        Livewire::actingAs($admin)
            ->test(ListVenues::class)
            ->assertCanSeeTableRecords([$independent, $clubVenue])
            ->filterTable('club_link', false)
            ->assertCanSeeTableRecords([$independent])
            ->assertCanNotSeeTableRecords([$clubVenue])
            ->filterTable('club_link', true)
            ->assertCanSeeTableRecords([$clubVenue])
            ->assertCanNotSeeTableRecords([$independent]);
    }
}
