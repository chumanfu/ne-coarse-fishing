<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FishingSessionPegLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_session_with_new_peg_on_pond_map(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create([
            'map_image_path' => 'water-maps/pond.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/pond.jpg', 'fake');

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => $water->id,
            'fished_at' => now()->toDateString(),
            'peg_mode' => 'new',
            'peg_number' => 'Island 4',
            'peg_map_x' => 42.5,
            'peg_map_y' => 61.25,
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $response->assertRedirect(route('sessions.show', $session));

        $this->assertTrue($session->hasPegLocation());
        $this->assertSame('Island 4', $session->peg_number);
        $this->assertNotNull($session->water_peg_id);
        $this->assertEqualsWithDelta(42.5, $session->waterPeg->map_x, 0.01);
        $this->assertEqualsWithDelta(61.25, $session->waterPeg->map_y, 0.01);
    }

    public function test_session_show_displays_peg_on_pond_map(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create([
            'map_image_path' => 'water-maps/pond.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/pond.jpg', 'fake');
        $peg = WaterPeg::factory()->for($water)->create([
            'number' => '12',
            'map_x' => 33.3,
            'map_y' => 44.4,
            'is_verified' => true,
        ]);

        $session = FishingSession::factory()
            ->for($user)
            ->for($venue)
            ->create([
                'water_id' => $water->id,
                'water_peg_id' => $peg->id,
                'peg_number' => '12',
            ]);

        $this->actingAs($user)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Peg location')
            ->assertSee('session-peg-view-map', false)
            ->assertSee('12');
    }

    public function test_user_can_update_session_without_peg(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create();
        $peg = WaterPeg::factory()->for($water)->create(['is_verified' => true]);
        $session = FishingSession::factory()
            ->for($user)
            ->for($venue)
            ->create([
                'water_id' => $water->id,
                'water_peg_id' => $peg->id,
                'peg_number' => '7',
            ]);

        $this->actingAs($user)
            ->patch(route('sessions.update', $session), [
                'venue_id' => $session->venue_id,
                'fished_at' => $session->fished_at->toDateString(),
                'peg_mode' => 'none',
                'peg_number' => 'Car park end',
            ])
            ->assertRedirect(route('sessions.show', $session));

        $session->refresh();

        $this->assertSame('Car park end', $session->peg_number);
        $this->assertNull($session->water_peg_id);
        $this->assertFalse($session->hasPegLocation());
    }

    public function test_create_form_includes_peg_controls(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('sessions.create'))
            ->assertOk()
            ->assertSee('Peg')
            ->assertSee('Existing peg')
            ->assertSee('Select peg on pond map', false)
            ->assertSee('selectPegFromMap', false)
            ->assertSee('existingMapPins', false)
            ->assertSee('Mark peg on pond map', false)
            ->assertSee('name="peg_photos[]" accept="image/*" multiple', false)
            ->assertSee('name="photos[]" accept="image/*" multiple', false)
            ->assertDontSee('capture=', false);
    }

    public function test_create_form_shows_existing_pegs_for_map_selection(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true, 'name' => 'Map Peg Lakes']);
        $water = Water::factory()->for($venue)->create([
            'name' => 'Match Lake',
            'map_image_path' => 'water-maps/pond.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/pond.jpg', 'fake');
        $peg = WaterPeg::factory()->for($water)->create([
            'number' => '7',
            'name' => 'Boilie Bay',
            'map_x' => 41.5,
            'map_y' => 62.25,
            'is_verified' => true,
        ]);

        $this->actingAs($user)
            ->get(route('sessions.create', ['venue' => $venue->slug]))
            ->assertOk()
            ->assertSee('Select peg on pond map', false)
            ->assertSee('selectPegFromMap', false)
            ->assertSee((string) $peg->id)
            ->assertSee('41.5', false)
            ->assertSee('Boilie Bay');
    }

    public function test_session_can_use_existing_mapped_peg(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create([
            'map_image_path' => 'water-maps/pond.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/pond.jpg', 'fake');
        $peg = WaterPeg::factory()->for($water)->create([
            'number' => '3',
            'map_x' => 20,
            'map_y' => 80,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => $water->id,
            'fished_at' => now()->toDateString(),
            'peg_mode' => 'existing',
            'water_peg_id' => $peg->id,
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertSame($peg->id, $session->water_peg_id);
    }
}
