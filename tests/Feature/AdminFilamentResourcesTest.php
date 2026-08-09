<?php

namespace Tests\Feature;

use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\ClubClaims\ClubClaimResource;
use App\Filament\Resources\ClubEditRequests\ClubEditRequestResource;
use App\Filament\Resources\MatchReports\MatchReportResource;
use App\Filament\Resources\MessageThreads\MessageThreadResource;
use App\Filament\Resources\SiteAnnouncements\SiteAnnouncementResource;
use App\Filament\Resources\TackleReviews\TackleReviewResource;
use App\Filament\Resources\TackleShopClaims\TackleShopClaimResource;
use App\Filament\Resources\TackleShopEditRequests\TackleShopEditRequestResource;
use App\Filament\Resources\VenuePhotos\VenuePhotoResource;
use App\Filament\Resources\VenueTactics\VenueTacticResource;
use App\Filament\Resources\WaterPegs\WaterPegResource;
use App\Filament\Resources\Waters\WaterResource;
use App\Filament\Resources\WaterVideos\WaterVideoResource;
use App\Models\Activity;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminFilamentResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_new_admin_resource_indexes(): void
    {
        Storage::fake(Uploads::diskName());
        Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');

        $admin = User::factory()->create();
        $admin->syncRoles(['super_admin']);

        $this->actingAs($admin);

        $resources = [
            ActivityResource::class,
            WaterResource::class,
            WaterPegResource::class,
            MatchReportResource::class,
            AnnouncementResource::class,
            SiteAnnouncementResource::class,
            MessageThreadResource::class,
            ClubClaimResource::class,
            TackleShopClaimResource::class,
            ClubEditRequestResource::class,
            TackleShopEditRequestResource::class,
            VenueTacticResource::class,
            VenuePhotoResource::class,
            WaterVideoResource::class,
            TackleReviewResource::class,
        ];

        foreach ($resources as $resource) {
            $this->assertTrue($resource::canAccess());
            $this->get($resource::getUrl('index'))->assertOk();
        }

        $water = Water::factory()->create([
            'map_image_path' => 'water-maps/admin-pond.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/admin-pond.jpg', 'fake');
        $peg = WaterPeg::factory()->for($water)->create([
            'map_x' => 40,
            'map_y' => 55,
        ]);

        $this->get(WaterPegResource::getUrl('edit', ['record' => $peg]))
            ->assertOk()
            ->assertSee('Map X %', false)
            ->assertSee('Map Y %', false)
            ->assertSee($water->mapImageUrl(), false)
            ->assertSee('Zoom in')
            ->assertSee('pondMapPlacer')
            ->assertDontSee('Upload a pond map image on the water record first', false);

        $this->get(WaterPegResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Select a water first to place the peg on its pond map');
    }

    public function test_super_admin_dashboard_shows_searchable_activity(): void
    {
        Role::findOrCreate('super_admin');

        $admin = User::factory()->create();
        $admin->syncRoles(['super_admin']);
        $angler = User::factory()->create(['name' => 'Chris Angler']);
        $venue = Venue::factory()->create(['is_approved' => true, 'name' => 'Legends Lake']);

        $sessionActivity = Activity::query()->create([
            'type' => Activity::TYPE_SESSION,
            'subject_type' => Venue::class,
            'subject_id' => $venue->id,
            'user_id' => $angler->id,
            'title' => 'Chris Angler logged a session at Legends Lake',
            'summary' => 'Peg 12',
            'url' => '/venues/'.$venue->slug,
        ]);

        $clubActivity = Activity::query()->create([
            'type' => Activity::TYPE_CLUB,
            'subject_type' => Venue::class,
            'subject_id' => $venue->id,
            'user_id' => null,
            'title' => 'Club added: Durham Anglers',
            'summary' => 'Durham',
            'url' => '/clubs/durham-anglers',
        ]);

        $this->actingAs($admin);

        $this->get(ActivityResource::getUrl('index'))->assertOk();

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\LatestActivityTable::class)
            ->assertSee('Site activity')
            ->assertSee('Chris Angler logged a session at Legends Lake')
            ->assertSee('Club added: Durham Anglers')
            ->filterTable('type', Activity::TYPE_SESSION)
            ->assertCanSeeTableRecords([$sessionActivity])
            ->assertCanNotSeeTableRecords([$clubActivity])
            ->resetTableFilters()
            ->searchTable('Durham Anglers')
            ->assertCanSeeTableRecords([$clubActivity])
            ->assertCanNotSeeTableRecords([$sessionActivity]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Activities\Pages\ManageActivities::class)
            ->assertCanSeeTableRecords([$sessionActivity, $clubActivity])
            ->filterTable('type', Activity::TYPE_CLUB)
            ->assertCanSeeTableRecords([$clubActivity])
            ->assertCanNotSeeTableRecords([$sessionActivity]);
    }

    public function test_angler_cannot_access_admin_water_pegs(): void
    {
        Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');

        $angler = User::factory()->create();
        $angler->syncRoles(['angler']);

        $this->actingAs($angler);

        $this->assertFalse(WaterPegResource::canAccess());
        $this->get(WaterPegResource::getUrl('index'))->assertForbidden();
    }
}
