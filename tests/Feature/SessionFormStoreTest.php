<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionFormStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_session_with_empty_default_catch_row(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();

        $response = $this->actingAs($user)->from(route('sessions.create'))->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => 'all',
            'peg_mode' => 'existing',
            'water_peg_id' => '',
            'fished_at' => now()->toDateString(),
            'duration_hours' => '',
            'weather' => '',
            'peg_number' => '',
            'peg_name' => '',
            'peg_latitude' => '',
            'peg_longitude' => '',
            'commentary' => 'Quiet morning.',
            'tactics_tip' => '',
            'catches' => [
                ['species_id' => '', 'weight_lb' => '', 'bait' => '', 'quantity' => 1],
            ],
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertSame('Quiet morning.', $session->commentary);
        $this->assertCount(0, $session->catches);
    }

    public function test_can_save_session_with_filled_catch_row(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();
        $species = Species::factory()->create([
            'name' => 'Test Roach',
            'slug' => 'test-roach-'.uniqid(),
        ]);

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => 'all',
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'catches' => [
                ['species_id' => '', 'weight_lb' => '', 'bait' => '', 'quantity' => 1],
                ['species_id' => $species->id, 'weight_lb' => '3.5', 'bait' => 'Maggot', 'quantity' => 2],
            ],
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertDatabaseHas('session_catches', [
            'fishing_session_id' => $session->id,
            'species_id' => $species->id,
            'weight_lb' => 3.5,
            'bait' => 'Maggot',
            'quantity' => 2,
        ]);
        $this->assertCount(1, $session->catches);
    }
}
