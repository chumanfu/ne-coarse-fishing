<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\SessionCatch;
use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Services\PegCatchStatsService;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PondMapZoomAndPegStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_pond_map_includes_zoom_controls_and_peg_explorer(): void
    {
        Storage::fake(Uploads::diskName());

        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create([
            'name' => 'Match Lake',
            'map_image_path' => 'water-maps/match.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/match.jpg', 'fake');

        WaterPeg::factory()->for($water)->create([
            'number' => '4',
            'name' => 'Island',
            'map_x' => 40,
            'map_y' => 55,
            'is_verified' => true,
            'description' => 'Deep water on the island side.',
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Zoom in')
            ->assertSee('pondMapExplorer', false)
            ->assertSee('click a pin for catch stats')
            ->assertSee('Deep water on the island side.', false);
    }

    public function test_peg_catch_stats_service_aggregates_fish_and_top_species(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create();
        $peg = WaterPeg::factory()->for($water)->create([
            'number' => '7',
            'map_x' => 30,
            'map_y' => 40,
            'is_verified' => true,
        ]);
        $carp = Species::factory()->create(['name' => 'Test Carp '.uniqid(), 'slug' => 'carp-test-'.uniqid()]);
        $roach = Species::factory()->create(['name' => 'Test Roach '.uniqid(), 'slug' => 'roach-test-'.uniqid()]);

        $sessionA = FishingSession::factory()->for($user)->for($venue)->create([
            'water_id' => $water->id,
            'water_peg_id' => $peg->id,
        ]);
        $sessionB = FishingSession::factory()->for($user)->for($venue)->create([
            'water_id' => $water->id,
            'water_peg_id' => $peg->id,
        ]);

        SessionCatch::factory()->for($sessionA)->create([
            'species_id' => $carp->id,
            'quantity' => 3,
            'weight_lb' => 12.5,
        ]);
        SessionCatch::factory()->for($sessionA)->create([
            'species_id' => $roach->id,
            'quantity' => 2,
            'weight_lb' => 1.1,
        ]);
        SessionCatch::factory()->for($sessionB)->create([
            'species_id' => $carp->id,
            'quantity' => 1,
            'weight_lb' => 18.25,
        ]);

        $payload = app(PegCatchStatsService::class)->mapPayloads(collect([$peg->load('photos')]));

        $this->assertCount(1, $payload);
        $this->assertSame(6, $payload[0]['fish_caught']);
        $this->assertSame(2, $payload[0]['session_count']);
        $this->assertEqualsWithDelta(18.25, $payload[0]['heaviest_lb'], 0.01);
        $this->assertSame($carp->name, $payload[0]['top_species'][0]['name']);
        $this->assertSame(4, $payload[0]['top_species'][0]['total']);
        $this->assertSame($roach->name, $payload[0]['top_species'][1]['name']);
        $this->assertSame(2, $payload[0]['top_species'][1]['total']);
    }

    public function test_venue_show_embeds_peg_stats_in_map_payload(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create([
            'map_image_path' => 'water-maps/lake.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/lake.jpg', 'fake');
        $peg = WaterPeg::factory()->for($water)->create([
            'number' => '2',
            'name' => 'Point',
            'map_x' => 22,
            'map_y' => 33,
            'is_verified' => true,
        ]);
        $species = Species::factory()->create(['name' => 'Test Bream '.uniqid(), 'slug' => 'bream-test-'.uniqid()]);
        $session = FishingSession::factory()->for($user)->for($venue)->create([
            'water_id' => $water->id,
            'water_peg_id' => $peg->id,
        ]);
        SessionCatch::factory()->for($session)->create([
            'species_id' => $species->id,
            'quantity' => 5,
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Fish caught')
            ->assertSee('Top species')
            ->assertSee('fish_caught', false)
            ->assertSee($species->name, false);
    }
}
