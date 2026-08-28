<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\TackleShop;
use App\Models\Venue;
use App\Models\VenuePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HomeSectionOrderTest extends TestCase
{
    use RefreshDatabase;

    private function getHomeHtml(): string
    {
        Http::fake(['api.open-meteo.com/*' => Http::response([], 503)]);

        return $this->get(route('home'))->assertOk()->getContent();
    }

    public function test_home_page_featured_sections_show_up_to_six_items(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response([], 503)]);

        Club::query()->update(['is_featured' => false]);
        TackleShop::query()->update(['is_featured' => false]);

        foreach (range(1, 7) as $i) {
            // Newest first via latest() — push these ahead of any seeded venues.
            $venue = Venue::factory()->create([
                'name' => "Grid Venue {$i}",
                'is_approved' => true,
                'created_at' => now()->addMinutes($i),
            ]);
            VenuePhoto::query()->create([
                'venue_id' => $venue->id,
                'image_path' => "images/venues/grid-venue-{$i}.jpg",
                'sort_order' => 0,
            ]);

            Club::factory()->featured()->create([
                'name' => "Grid Club {$i}",
                'sort_order' => $i,
                'logo_path' => "images/clubs/grid-club-{$i}.png",
            ]);

            TackleShop::factory()->featured()->create([
                'name' => "Grid Shop {$i}",
                'sort_order' => $i,
                'logo_path' => "images/tackle-shops/grid-shop-{$i}.png",
            ]);
        }

        // Featured without image/logo must be excluded even when newer / lower sort_order.
        Venue::factory()->create([
            'name' => 'No Photo Venue',
            'is_approved' => true,
            'created_at' => now()->addHour(),
        ]);
        Club::factory()->featured()->create([
            'name' => 'No Logo Club',
            'sort_order' => 0,
            'logo_path' => null,
        ]);
        TackleShop::factory()->featured()->create([
            'name' => 'No Logo Shop',
            'sort_order' => 0,
            'logo_path' => null,
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $venuesSection = $this->sectionHtml($html, 'home-section--venues', 'home-section--clubs');
        $clubsSection = $this->sectionHtml($html, 'home-section--clubs', 'home-section--shops');
        $shopsSection = $this->sectionHtml($html, 'home-section--shops', 'home-board');

        // Venues: latest() keeps 7..2, drops oldest (1).
        foreach (range(2, 7) as $i) {
            $this->assertStringContainsString("Grid Venue {$i}", $venuesSection);
            $this->assertStringContainsString("images/venues/grid-venue-{$i}.jpg", $venuesSection);
        }
        $this->assertStringNotContainsString('Grid Venue 1', $venuesSection);
        $this->assertStringNotContainsString('No Photo Venue', $venuesSection);
        $this->assertStringNotContainsString('Photo coming soon', $venuesSection);

        // Clubs/shops: ordered() keeps sort_order 1..6, drops 7.
        foreach (range(1, 6) as $i) {
            $this->assertStringContainsString("Grid Club {$i}", $clubsSection);
            $this->assertStringContainsString("images/clubs/grid-club-{$i}.png", $clubsSection);
            $this->assertStringContainsString("Grid Shop {$i}", $shopsSection);
            $this->assertStringContainsString("images/tackle-shops/grid-shop-{$i}.png", $shopsSection);
        }
        $this->assertStringNotContainsString('Grid Club 7', $clubsSection);
        $this->assertStringNotContainsString('Grid Shop 7', $shopsSection);
        $this->assertStringNotContainsString('No Logo Club', $clubsSection);
        $this->assertStringNotContainsString('No Logo Shop', $shopsSection);
        $this->assertStringNotContainsString('Club logo soon', $clubsSection);
        $this->assertStringNotContainsString('Shop logo soon', $shopsSection);
    }

    private function sectionHtml(string $html, string $startMarker, string $endMarker): string
    {
        $start = strpos($html, $startMarker);
        $end = strpos($html, $endMarker);

        $this->assertNotFalse($start, "Section start marker [{$startMarker}] not found");
        $this->assertNotFalse($end, "Section end marker [{$endMarker}] not found");
        $this->assertLessThan($end, $start, "Section markers out of order: {$startMarker} → {$endMarker}");

        return substr($html, $start, $end - $start);
    }

    public function test_home_page_shows_intro_section_before_featured_venues(): void
    {
        $html = $this->getHomeHtml();

        $introPos  = strpos($html, 'home-intro');
        $venuesPos = strpos($html, 'home-section--venues');

        $this->assertNotFalse($introPos, 'home-intro section not found');
        $this->assertNotFalse($venuesPos, 'home-section--venues section not found');
        $this->assertLessThan($venuesPos, $introPos, 'Intro section should appear before the featured-venues section');
    }

    public function test_home_page_shows_venues_before_clubs(): void
    {
        $html = $this->getHomeHtml();

        $venuesPos = strpos($html, 'home-section--venues');
        $clubsPos  = strpos($html, 'home-section--clubs');

        $this->assertNotFalse($venuesPos, 'home-section--venues not found');
        $this->assertNotFalse($clubsPos, 'home-section--clubs not found');
        $this->assertLessThan($clubsPos, $venuesPos, 'Featured Venues section should appear before Angling Clubs section');
    }

    public function test_home_page_shows_clubs_before_tackle_shops(): void
    {
        $html = $this->getHomeHtml();

        $clubsPos = strpos($html, 'home-section--clubs');
        $shopsPos = strpos($html, 'home-section--shops');

        $this->assertNotFalse($clubsPos, 'home-section--clubs not found');
        $this->assertNotFalse($shopsPos, 'home-section--shops not found');
        $this->assertLessThan($shopsPos, $clubsPos, 'Angling Clubs section should appear before Tackle Shops section');
    }

    public function test_home_page_shows_weather_inside_map_section_after_tackle_shops(): void
    {
        $html = $this->getHomeHtml();

        $shopsPos   = strpos($html, 'home-section--shops');
        $mapPos     = strpos($html, 'home-board');
        $weatherPos = strpos($html, 'Weather along the bank');

        $this->assertNotFalse($shopsPos, 'home-section--shops not found');
        $this->assertNotFalse($mapPos, 'home-board (map) section not found');
        $this->assertNotFalse($weatherPos, 'Weather strip not found');
        $this->assertLessThan($mapPos, $shopsPos, 'Tackle Shops section should appear before the map/weather section');
        $this->assertGreaterThan($mapPos, $weatherPos, 'Weather strip should appear inside the map section');
    }

    public function test_home_page_intro_contains_about_copy(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response([], 503)]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('About this site')
            ->assertSee('Built by an angler')
            ->assertSee('Read the full story');
    }

    public function test_home_page_featured_venues_section_heading_visible(): void
    {
        $html = $this->getHomeHtml();

        $this->assertStringContainsString('Featured Venues', $html);
    }

    public function test_home_page_angling_clubs_section_heading_visible(): void
    {
        $html = $this->getHomeHtml();

        $this->assertStringContainsString('Angling Clubs', $html);
    }

    public function test_home_page_tackle_shops_section_heading_visible(): void
    {
        $html = $this->getHomeHtml();

        $this->assertStringContainsString('Tackle Shops', $html);
    }

    public function test_home_page_venue_section_contains_view_venue_cta_text(): void
    {
        $html = $this->getHomeHtml();

        // The CTA button text is present in the venues section template
        $this->assertStringContainsString('View Venue', $html);
    }
}
