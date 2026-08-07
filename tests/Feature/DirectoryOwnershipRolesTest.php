<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubClaim;
use App\Models\TackleShop;
use App\Models\TackleShopClaim;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DirectoryOwnershipRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'angler', 'fishery_manager', 'club_owner', 'tackle_shop_owner'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_only_club_owner_or_super_admin_can_edit_club(): void
    {
        Storage::fake(config('filesystems.uploads'));

        $clubOwner = User::factory()->create();
        $clubOwner->assignRole(['angler', 'club_owner']);

        $fisheryManager = User::factory()->create();
        $fisheryManager->assignRole(['angler', 'fishery_manager']);

        $club = Club::factory()->create([
            'is_published' => true,
            'manager_id' => $clubOwner->id,
            'manager_verified' => true,
            'town' => 'Durham',
        ]);

        $this->actingAs($clubOwner)
            ->patch(route('clubs.update', $club), [
                'name' => $club->name,
                'town' => 'Newcastle',
                'overview' => 'Club owner update',
            ])
            ->assertRedirect();

        $this->assertSame('Newcastle', $club->fresh()->town);

        $this->actingAs($fisheryManager)
            ->patch(route('clubs.update', $club), [
                'name' => $club->name,
                'town' => 'Sunderland',
                'overview' => 'Should fail',
            ])
            ->assertForbidden();

        $this->assertSame('Newcastle', $club->fresh()->town);
    }

    public function test_manager_id_without_club_owner_role_cannot_edit_club(): void
    {
        $user = User::factory()->create();
        $user->assignRole('angler');

        $club = Club::factory()->create([
            'is_published' => true,
            'manager_id' => $user->id,
            'manager_verified' => true,
        ]);

        $this->actingAs($user)
            ->get(route('clubs.edit', $club))
            ->assertForbidden();
    }

    public function test_only_tackle_shop_owner_or_super_admin_can_edit_shop(): void
    {
        $shopOwner = User::factory()->create();
        $shopOwner->assignRole(['angler', 'tackle_shop_owner']);

        $fisheryManager = User::factory()->create();
        $fisheryManager->assignRole(['angler', 'fishery_manager']);

        $shop = TackleShop::factory()->create([
            'is_published' => true,
            'manager_id' => $shopOwner->id,
            'manager_verified' => true,
            'town' => 'Gateshead',
        ]);

        $this->actingAs($shopOwner)
            ->patch(route('tackle-shops.update', $shop), [
                'name' => $shop->name,
                'url' => $shop->url,
                'location_type' => $shop->location_type,
                'town' => 'Newcastle',
            ])
            ->assertRedirect();

        $this->assertSame('Newcastle', $shop->fresh()->town);

        $this->actingAs($fisheryManager)
            ->patch(route('tackle-shops.update', $shop), [
                'name' => $shop->name,
                'url' => $shop->url,
                'location_type' => $shop->location_type,
                'town' => 'Sunderland',
            ])
            ->assertForbidden();
    }

    public function test_club_owner_can_edit_venue_linked_to_their_club_but_not_unlinked_venues(): void
    {
        $clubOwner = User::factory()->create();
        $clubOwner->assignRole(['angler', 'club_owner']);

        $club = Club::factory()->create([
            'is_published' => true,
            'manager_id' => $clubOwner->id,
            'manager_verified' => true,
        ]);

        $ownedVenue = Venue::factory()->create([
            'is_approved' => true,
            'manager_id' => null,
            'name' => 'Club Lake',
        ]);
        $club->venues()->attach($ownedVenue);

        $otherVenue = Venue::factory()->create([
            'is_approved' => true,
            'manager_id' => null,
            'name' => 'Private Lake',
        ]);

        $this->actingAs($clubOwner)
            ->get(route('venues.edit', $ownedVenue))
            ->assertOk();

        $this->actingAs($clubOwner)
            ->get(route('venues.edit', $otherVenue))
            ->assertForbidden();
    }

    public function test_fishery_manager_cannot_edit_club_even_when_managing_a_venue(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(['angler', 'fishery_manager']);

        $club = Club::factory()->create([
            'is_published' => true,
            'manager_id' => null,
        ]);

        $venue = Venue::factory()->create([
            'is_approved' => true,
            'manager_id' => $manager->id,
            'manager_verified' => true,
        ]);
        $club->venues()->attach($venue);

        $this->actingAs($manager)
            ->get(route('venues.edit', $venue))
            ->assertOk();

        $this->actingAs($manager)
            ->get(route('clubs.edit', $club))
            ->assertForbidden();
    }

    public function test_approving_club_claim_assigns_club_owner_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $claimer = User::factory()->create();
        $claimer->assignRole('angler');

        $club = Club::factory()->create(['is_published' => true, 'manager_id' => null]);
        $claim = ClubClaim::query()->create([
            'club_id' => $club->id,
            'user_id' => $claimer->id,
            'message' => 'I run this club',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ClubClaims\Pages\ListClubClaims::class)
            ->callTableAction('approve', $claim);

        $this->assertTrue($claimer->fresh()->hasRole('club_owner'));
        $this->assertFalse($claimer->fresh()->hasRole('fishery_manager'));
        $this->assertSame($claimer->id, $club->fresh()->manager_id);
    }

    public function test_approving_tackle_shop_claim_assigns_tackle_shop_owner_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $claimer = User::factory()->create();
        $claimer->assignRole('angler');

        $shop = TackleShop::factory()->create(['is_published' => true, 'manager_id' => null]);
        $claim = TackleShopClaim::query()->create([
            'tackle_shop_id' => $shop->id,
            'user_id' => $claimer->id,
            'message' => 'I own this shop',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\TackleShopClaims\Pages\ListTackleShopClaims::class)
            ->callTableAction('approve', $claim);

        $this->assertTrue($claimer->fresh()->hasRole('tackle_shop_owner'));
        $this->assertFalse($claimer->fresh()->hasRole('fishery_manager'));
        $this->assertSame($claimer->id, $shop->fresh()->manager_id);
    }

    public function test_user_can_hold_multiple_owner_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['angler', 'club_owner', 'tackle_shop_owner', 'fishery_manager']);

        $this->assertTrue($user->hasAllRoles(['angler', 'club_owner', 'tackle_shop_owner', 'fishery_manager']));
        $this->assertTrue($user->canAccessPanel(filament()->getDefaultPanel()));
    }
}
