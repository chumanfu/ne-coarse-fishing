<?php

namespace Tests\Feature;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeVenueMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_includes_venue_map_with_search(): void
    {
        Venue::factory()->create([
            'name' => 'Home Map Mere',
            'is_approved' => true,
            'latitude' => 55.01,
            'longitude' => -1.55,
            'overview' => 'A quiet day-ticket water for the home map.',
        ]);

        Venue::factory()->create([
            'name' => 'Unapproved Hidden Pool',
            'is_approved' => false,
            'latitude' => 55.02,
            'longitude' => -1.56,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Explore the map')
            ->assertSee('Search map')
            ->assertSee('home-venue-map', false)
            ->assertSee('Latest activity')
            ->assertSee('Featured Venues')
            ->assertSee('Angling Clubs')
            ->assertSee('Tackle Shops')
            ->assertSee('Home Map Mere')
            ->assertSee('View Venue')
            ->assertDontSee('"name":"Unapproved Hidden Pool"', false);
    }
}
