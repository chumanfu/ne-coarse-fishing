<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreadcrumbNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_show_includes_venues_breadcrumb(): void
    {
        $venue = Venue::factory()->create([
            'name' => 'Crumb Lake',
            'is_approved' => true,
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee(route('venues.index'), false)
            ->assertSee('Venues')
            ->assertSee('Crumb Lake');
    }

    public function test_session_show_includes_venue_breadcrumb(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create([
            'name' => 'Breadcrumb Mere',
            'is_approved' => true,
        ]);
        $session = FishingSession::factory()
            ->for($user)
            ->for($venue)
            ->create([
                'fished_at' => '2026-08-09',
            ]);

        $this->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee(route('venues.index'), false)
            ->assertSee(route('venues.show', $venue), false)
            ->assertSee('Venues')
            ->assertSee('Breadcrumb Mere')
            ->assertSee('09 Aug 2026')
            ->assertDontSee('Back to venue');
    }

    public function test_session_create_with_venue_includes_breadcrumb(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create([
            'name' => 'Trail Pond',
            'is_approved' => true,
        ]);

        $this->actingAs($user)
            ->get(route('sessions.create', ['venue' => $venue->slug]))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee(route('venues.show', $venue), false)
            ->assertSee('Trail Pond')
            ->assertSee('Log session');
    }
}
