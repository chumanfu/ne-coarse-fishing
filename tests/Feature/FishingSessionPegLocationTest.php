<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FishingSessionPegLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_session_with_peg_name_and_map_pin(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create([
            'latitude' => 55.03154,
            'longitude' => -1.56987,
        ]);

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'fished_at' => now()->toDateString(),
            'peg_number' => 'Island 4',
            'peg_latitude' => 55.03180,
            'peg_longitude' => -1.57010,
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $response->assertRedirect(route('sessions.show', $session));

        $this->assertTrue($session->hasPegLocation());
        $this->assertSame('Island 4', $session->peg_number);
        $this->assertEqualsWithDelta(55.03180, $session->peg_latitude, 0.00001);
        $this->assertEqualsWithDelta(-1.57010, $session->peg_longitude, 0.00001);
    }

    public function test_session_show_displays_peg_map_when_location_set(): void
    {
        $user = User::factory()->create();
        $session = FishingSession::factory()
            ->for($user)
            ->withPegLocation(55.1, -1.6)
            ->create([
                'peg_number' => 'Peg 12',
            ]);

        $this->actingAs($user)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Peg location')
            ->assertSee('Peg 12')
            ->assertSee('session-peg-view-map');
    }

    public function test_user_can_update_and_clear_peg_location(): void
    {
        $user = User::factory()->create();
        $session = FishingSession::factory()
            ->for($user)
            ->withPegLocation()
            ->create([
                'peg_number' => '7',
            ]);

        $this->actingAs($user)
            ->patch(route('sessions.update', $session), [
                'venue_id' => $session->venue_id,
                'fished_at' => $session->fished_at->toDateString(),
                'peg_number' => 'Car park end',
                'peg_latitude' => '',
                'peg_longitude' => '',
            ])
            ->assertRedirect(route('sessions.show', $session));

        $session->refresh();

        $this->assertSame('Car park end', $session->peg_number);
        $this->assertFalse($session->hasPegLocation());
    }

    public function test_create_form_includes_peg_map(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('sessions.create'))
            ->assertOk()
            ->assertSee('Peg')
            ->assertSee('session-peg-map')
            ->assertSee('Existing peg');
    }
}
