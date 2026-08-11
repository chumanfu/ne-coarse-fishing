<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionIndexHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_sessions_index_shows_hero_banner(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true, 'name' => 'Hero Lake']);
        FishingSession::factory()->for($user)->for($venue)->create();

        $this->actingAs($user)
            ->get(route('sessions.index'))
            ->assertOk()
            ->assertSee('page-hero', false)
            ->assertSee('My session logs')
            ->assertSee('images/sessions/hero-bankside.jpg', false)
            ->assertSee('Log a session')
            ->assertSee('1 session logged')
            ->assertSee('Hero Lake');
    }

    public function test_sessions_index_hero_shows_empty_state_without_count(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('sessions.index'))
            ->assertOk()
            ->assertSee('page-hero', false)
            ->assertSee('No sessions yet')
            ->assertDontSee('session logged');
    }
}
