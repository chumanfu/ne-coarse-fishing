<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueEditRequest;
use App\Models\VenueTactic;
use App\Services\VenuePersistenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FishingSessionTacticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_log_session_with_tactics_tip(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'fished_at' => now()->toDateString(),
            'tactics_tip' => 'Method feeder on peg 8 with 8mm pellet.',
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $response->assertRedirect(route('sessions.show', $session));

        $this->assertDatabaseHas('venue_tactics', [
            'fishing_session_id' => $session->id,
            'body' => 'Method feeder on peg 8 with 8mm pellet.',
        ]);
    }

    public function test_tactics_tip_appears_on_venue_page(): void
    {
        $user = User::factory()->create(['name' => 'Chris M']);
        $venue = Venue::factory()->create(['name' => 'Test Lakes', 'tactics_guide' => 'Official club rules apply.']);

        VenueTactic::query()->create([
            'venue_id' => $venue->id,
            'user_id' => $user->id,
            'body' => 'Waggler and maggot on the far bank.',
            'fished_at' => now()->subDays(2),
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Official guide')
            ->assertSee('Official club rules apply.')
            ->assertSee('Angler tips')
            ->assertSee('Waggler and maggot on the far bank.')
            ->assertSee('Chris M');
    }

    public function test_guests_see_login_prompt_in_tactics_section(): void
    {
        $venue = Venue::factory()->create();

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Log in to share tactics')
            ->assertDontSee('Share a tactic');
    }

    public function test_user_can_add_standalone_tactic(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();

        $this->actingAs($user)
            ->post(route('tactics.store', $venue), [
                'body' => 'Pole and maggot on the island.',
                'peg_number' => '14',
            ])
            ->assertRedirect(route('venues.show', $venue));

        $this->assertDatabaseHas('venue_tactics', [
            'venue_id' => $venue->id,
            'user_id' => $user->id,
            'body' => 'Pole and maggot on the island.',
            'fishing_session_id' => null,
        ]);
    }

    public function test_user_can_edit_and_delete_own_session(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();
        $session = FishingSession::factory()->create([
            'user_id' => $user->id,
            'venue_id' => $venue->id,
            'commentary' => 'Original notes.',
        ]);

        $this->actingAs($user)
            ->get(route('sessions.edit', $session))
            ->assertOk()
            ->assertSee('Edit fishing session')
            ->assertSee('Original notes.');

        $this->actingAs($user)
            ->get(route('sessions.create'))
            ->assertOk()
            ->assertSee('Log a fishing session');

        $this->actingAs($user)
            ->patch(route('sessions.update', $session), [
                'venue_id' => $venue->id,
                'fished_at' => $session->fished_at->toDateString(),
                'commentary' => 'Updated notes.',
            ])
            ->assertRedirect(route('sessions.show', $session));

        $this->assertDatabaseHas('fishing_sessions', [
            'id' => $session->id,
            'commentary' => 'Updated notes.',
        ]);

        $this->actingAs($user)
            ->delete(route('sessions.destroy', $session))
            ->assertRedirect(route('sessions.index'));

        $this->assertDatabaseMissing('fishing_sessions', ['id' => $session->id]);
    }
}
