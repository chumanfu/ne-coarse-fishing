<?php

namespace Tests\Feature;

use App\Filament\Resources\WaterVideos\Pages\CreateWaterVideo;
use App\Filament\Resources\WaterVideos\Pages\ListWaterVideos;
use App\Filament\Resources\WaterVideos\WaterVideoResource;
use App\Models\User;
use App\Models\Water;
use App\Models\WaterVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminWaterVideosTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_and_approve_water_videos(): void
    {
        Role::findOrCreate('super_admin');

        $admin = User::factory()->create();
        $admin->syncRoles(['super_admin']);
        $water = Water::factory()->create();

        $this->actingAs($admin);

        $this->get(WaterVideoResource::getUrl('index'))->assertOk();
        $this->get(WaterVideoResource::getUrl('create'))->assertOk();

        Livewire::actingAs($admin)
            ->test(CreateWaterVideo::class)
            ->fillForm([
                'water_id' => $water->id,
                'user_id' => $admin->id,
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'title' => 'Admin lake clip',
                'is_approved' => false,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $video = WaterVideo::query()->where('water_id', $water->id)->first();
        $this->assertNotNull($video);
        $this->assertFalse($video->is_approved);
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_id);

        Livewire::actingAs($admin)
            ->test(ListWaterVideos::class)
            ->callTableAction('approve', $video);

        $this->assertTrue($video->fresh()->is_approved);
        $this->assertSame($admin->id, $video->fresh()->approved_by);
    }
}
