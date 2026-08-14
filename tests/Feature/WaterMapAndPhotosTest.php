<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPhoto;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WaterMapAndPhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_upload_pond_map_and_it_clears_peg_positions(): void
    {
        Storage::fake(Uploads::diskName());
        Role::findOrCreate('fishery_manager');

        $manager = User::factory()->create();
        $manager->assignRole('fishery_manager');
        $venue = Venue::factory()->create([
            'is_approved' => true,
            'manager_id' => $manager->id,
        ]);
        $water = Water::factory()->for($venue)->create();
        $peg = $water->pegs()->create([
            'created_by' => $manager->id,
            'number' => '1',
            'map_x' => 20,
            'map_y' => 30,
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        $this->actingAs($manager)
            ->post(route('waters.map-image.update', [$venue, $water]), [
                'map_image' => UploadedFile::fake()->image('pond.jpg'),
            ])
            ->assertRedirect();

        $water->refresh();
        $peg->refresh();

        $this->assertNotNull($water->map_image_path);
        $this->assertNull($peg->map_x);
        $this->assertNull($peg->map_y);
        $this->assertNull($peg->latitude);
        $this->assertNull($peg->longitude);
    }

    public function test_angler_photo_requires_approval_before_public_display(): void
    {
        Storage::fake(Uploads::diskName());
        Role::findOrCreate('fishery_manager');

        $angler = User::factory()->create();
        $manager = User::factory()->create();
        $manager->assignRole('fishery_manager');
        $venue = Venue::factory()->create([
            'is_approved' => true,
            'manager_id' => $manager->id,
        ]);
        $water = Water::factory()->for($venue)->create(['name' => 'Specimen Lake']);

        $this->actingAs($angler)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Upload a photo of this water')
            ->assertSee('accept="image/*"', false)
            ->assertDontSee('capture=', false);

        $this->actingAs($angler)
            ->post(route('waters.photos.store', [$venue, $water]), [
                'photo' => UploadedFile::fake()->image('catch.jpg'),
            ])
            ->assertRedirect();

        $photo = WaterPhoto::query()->where('water_id', $water->id)->first();
        $this->assertNotNull($photo);
        $this->assertFalse($photo->is_approved);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('No approved photos yet.')
            ->assertDontSee('Pending approval');

        $this->actingAs($manager)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Pending approval');

        $this->actingAs($manager)
            ->post(route('waters.photos.approve', [$venue, $water, $photo]))
            ->assertRedirect();

        $this->assertTrue($photo->fresh()->is_approved);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertDontSee('No approved photos yet.')
            ->assertSee('Angler photos');
    }

    public function test_venue_page_shows_mapped_pegs_on_pond_image(): void
    {
        Storage::fake(Uploads::diskName());

        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create([
            'name' => 'Match Lake',
            'map_image_path' => 'water-maps/match.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/match.jpg', 'fake');
        $water->pegs()->create([
            'number' => '1',
            'name' => 'Peg 1',
            'map_x' => 40,
            'map_y' => 55,
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Pond map')
            ->assertSee($water->mapImageUrl(), false)
            ->assertSee('1 mapped peg');
    }
}
