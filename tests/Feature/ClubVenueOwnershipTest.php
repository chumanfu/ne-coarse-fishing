<?php

namespace Tests\Feature;

use App\Filament\Resources\Clubs\Pages\EditClub;
use App\Filament\Resources\Clubs\Pages\ListClubs;
use App\Filament\Resources\Venues\Pages\ListVenues;
use App\Livewire\VenueWizard;
use App\Models\Club;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClubVenueOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_show_displays_owning_club_link(): void
    {
        $club = Club::factory()->create([
            'name' => 'Wearside Ownership Club',
            'slug' => 'wearside-ownership-club',
            'is_published' => true,
        ]);

        $venue = Venue::factory()->create([
            'name' => 'Ownership Mere',
            'slug' => 'ownership-mere',
            'is_approved' => true,
        ]);

        $venue->clubs()->attach($club);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Owned by')
            ->assertSee('Club ownership')
            ->assertSee('Wearside Ownership Club')
            ->assertSee(route('clubs.show', $club), false);
    }

    public function test_club_show_displays_owned_venues(): void
    {
        $club = Club::factory()->create([
            'name' => 'Owned Waters Club',
            'slug' => 'owned-waters-club',
            'is_published' => true,
        ]);

        $venue = Venue::factory()->create([
            'name' => 'Club Owned Pond',
            'slug' => 'club-owned-pond',
            'is_approved' => true,
        ]);

        $club->venues()->attach($venue);

        $this->get(route('clubs.show', $club))
            ->assertOk()
            ->assertSee('Owned waters')
            ->assertSee('Venues owned or managed by this club.')
            ->assertSee('Club Owned Pond')
            ->assertSee(route('venues.show', $venue), false);
    }

    public function test_venue_directory_card_shows_club_owned_badge(): void
    {
        $club = Club::factory()->create(['is_published' => true]);
        $owned = Venue::factory()->create([
            'name' => 'Badge Owned Lake',
            'is_approved' => true,
        ]);
        $unowned = Venue::factory()->create([
            'name' => 'Independent Lake ZZ',
            'is_approved' => true,
        ]);

        $owned->clubs()->attach($club);

        $this->get(route('venues.index', ['q' => 'Badge Owned Lake']))
            ->assertOk()
            ->assertSee('Badge Owned Lake')
            ->assertSee('Club owned');

        $this->get(route('venues.index', ['q' => 'Independent Lake ZZ']))
            ->assertOk()
            ->assertSee('Independent Lake ZZ')
            ->assertDontSee('Club owned');
    }

    public function test_admin_can_attach_venues_from_club_edit_form(): void
    {
        $admin = $this->makeAdmin();
        $club = Club::factory()->create(['name' => 'Admin Sync Club']);
        $venue = Venue::factory()->create(['name' => 'Attached Water']);

        Livewire::actingAs($admin)
            ->test(EditClub::class, ['record' => $club->getRouteKey()])
            ->fillForm([
                'venues' => [$venue->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($club->fresh()->venues()->whereKey($venue->id)->exists());
    }

    public function test_admin_can_sync_owning_clubs_from_venue_wizard(): void
    {
        $admin = $this->makeAdmin();
        $club = Club::factory()->create(['name' => 'Wizard Linked Club']);
        $venue = Venue::factory()->create([
            'name' => 'Wizard Ownership Lake',
            'user_id' => $admin->id,
            'is_approved' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(VenueWizard::class, ['venue' => $venue->id, 'admin' => true])
            ->set('step', 5)
            ->set('waters.0.name', 'Main lake')
            ->set('clubIds', [$club->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($venue->fresh()->clubs()->whereKey($club->id)->exists());

        Livewire::actingAs($admin)
            ->test(VenueWizard::class, ['venue' => $venue->id, 'admin' => true])
            ->set('step', 5)
            ->set('waters.0.name', 'Main lake')
            ->set('clubIds', [])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($venue->fresh()->clubs()->whereKey($club->id)->exists());
    }

    public function test_admin_tables_show_ownership_columns(): void
    {
        $admin = $this->makeAdmin();
        $club = Club::factory()->create(['name' => 'Table Club Ownership']);
        $venue = Venue::factory()->create(['name' => 'Table Venue Ownership', 'is_approved' => true]);
        $club->venues()->attach($venue);

        Livewire::actingAs($admin)
            ->test(ListVenues::class)
            ->searchTable('Table Venue Ownership')
            ->assertCanSeeTableRecords([$venue])
            ->assertSee('Table Club Ownership');

        Livewire::actingAs($admin)
            ->test(ListClubs::class)
            ->searchTable('Table Club Ownership')
            ->assertCanSeeTableRecords([$club])
            ->assertSee('Table Venue Ownership');
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        Role::findOrCreate('super_admin');
        $admin->assignRole('super_admin');

        return $admin;
    }
}
