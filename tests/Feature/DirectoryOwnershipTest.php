<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubEditRequest;
use App\Models\TackleShop;
use App\Models\TackleShopEditRequest;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DirectoryOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');
        Role::findOrCreate('fishery_manager');
        Role::findOrCreate('club_owner');
        Role::findOrCreate('tackle_shop_owner');
    }

    public function test_angler_can_claim_and_suggest_club_edits(): void
    {
        $user = User::factory()->create();
        $user->assignRole('angler');
        $club = Club::factory()->create(['is_published' => true, 'name' => 'Old Club']);

        $this->actingAs($user)
            ->post(route('clubs.claim', $club), ['message' => 'I run this club'])
            ->assertRedirect(route('clubs.show', $club));

        $this->assertDatabaseHas('club_claims', [
            'club_id' => $club->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('clubs.suggest-edit.store', $club), [
                'name' => 'Updated Club Name',
                'town' => 'Durham',
                'message' => 'Name fix',
            ])
            ->assertRedirect(route('clubs.show', $club));

        $this->assertDatabaseHas('club_edit_requests', [
            'club_id' => $club->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_club_manager_can_update_details_and_logo(): void
    {
        Storage::fake(config('filesystems.uploads'));

        $manager = User::factory()->create();
        $manager->assignRole(['angler', 'club_owner']);
        $club = Club::factory()->create([
            'is_published' => true,
            'manager_id' => $manager->id,
            'manager_verified' => true,
        ]);

        $this->actingAs($manager)
            ->patch(route('clubs.update', $club), [
                'name' => $club->name,
                'town' => 'Newcastle',
                'overview' => 'Updated overview',
                'logo' => UploadedFile::fake()->image('logo.jpg'),
            ])
            ->assertRedirect();

        $club->refresh();
        $this->assertSame('Newcastle', $club->town);
        $this->assertSame('Updated overview', $club->overview);
        $this->assertNotNull($club->logo_path);
        $this->assertFalse(str_starts_with((string) $club->logo_path, 'images/'));
    }

    public function test_angler_can_claim_and_suggest_tackle_shop_edits(): void
    {
        $user = User::factory()->create();
        $user->assignRole('angler');
        $shop = TackleShop::factory()->create(['is_published' => true]);

        $this->actingAs($user)
            ->post(route('tackle-shops.claim', $shop))
            ->assertRedirect(route('tackle-shops.show', $shop));

        $this->assertDatabaseHas('tackle_shop_claims', [
            'tackle_shop_id' => $shop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('tackle-shops.suggest-edit.store', $shop), [
                'name' => 'Updated Shop',
                'url' => 'https://example.com/shop',
                'location_type' => 'local',
                'town' => 'Gateshead',
            ])
            ->assertRedirect(route('tackle-shops.show', $shop));

        $this->assertDatabaseHas('tackle_shop_edit_requests', [
            'tackle_shop_id' => $shop->id,
            'status' => 'pending',
        ]);
    }

    public function test_venue_water_section_includes_pond_map_and_pegs(): void
    {
        Storage::fake(Uploads::diskName());

        $venue = Venue::factory()->create([
            'is_approved' => true,
            'latitude' => 54.97,
            'longitude' => -1.61,
        ]);
        $water = Water::factory()->create([
            'venue_id' => $venue->id,
            'name' => 'Match Lake',
            'map_image_path' => 'water-maps/match.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/match.jpg', 'fake');
        WaterPeg::factory()->create([
            'water_id' => $water->id,
            'name' => 'Peg 1',
            'map_x' => 40,
            'map_y' => 55,
            'is_verified' => true,
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Pond map')
            ->assertSee($water->mapImageUrl(), false);
    }

    public function test_only_super_admin_user_resource_can_access_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $angler = User::factory()->create();
        $angler->assignRole('angler');

        $this->actingAs($admin);
        $this->assertTrue(\App\Filament\Resources\Users\UserResource::canAccess());

        $this->actingAs($angler);
        $this->assertFalse(\App\Filament\Resources\Users\UserResource::canAccess());
    }
}
