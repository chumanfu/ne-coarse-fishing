<?php

namespace Tests\Feature;

use App\Services\HomeWeatherService;
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
