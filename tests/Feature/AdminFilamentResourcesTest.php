<?php

namespace Tests\Feature;

use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\MatchReports\MatchReportResource;
use App\Filament\Resources\VenuePhotos\VenuePhotoResource;
use App\Filament\Resources\VenueTactics\VenueTacticResource;
use App\Filament\Resources\WaterPegs\WaterPegResource;
use App\Filament\Resources\Waters\WaterResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminFilamentResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_new_admin_resource_indexes(): void
    {
        Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');

        $admin = User::factory()->create();
        $admin->syncRoles(['super_admin']);

        $this->actingAs($admin);

        $resources = [
            WaterResource::class,
            WaterPegResource::class,
            MatchReportResource::class,
            AnnouncementResource::class,
            VenueTacticResource::class,
            VenuePhotoResource::class,
        ];

        foreach ($resources as $resource) {
            $this->assertTrue($resource::canAccess());
            $this->get($resource::getUrl('index'))->assertOk();
        }
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
