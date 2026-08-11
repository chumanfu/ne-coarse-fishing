<?php

namespace Tests\Feature;

use App\Services\HomeWeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HomeWeatherTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_weather_along_the_bank_section(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->openMeteoPayload(), 200),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Weather along the bank')
            ->assertSee('Berwick')
            ->assertSee('Morpeth')
            ->assertSee('Newcastle')
            ->assertSee('Sunderland')
            ->assertSee('Bishop Auckland')
            ->assertSee('Middlesbrough')
            ->assertSee('Thirsk')
            ->assertSee('Overcast')
            ->assertSee('16°C')
            ->assertSee('Tomorrow: Showers, 11–18°C');
    }

    public function test_home_page_shows_graceful_empty_state_when_weather_api_fails(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response('Unavailable', 503),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Weather along the bank')
            ->assertSee('Weather is unavailable right now')
            ->assertDontSee('16°C');
    }

    public function test_home_weather_service_caches_successful_responses(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->openMeteoPayload(), 200),
        ]);

        $service = app(HomeWeatherService::class);

        $first = $service->locations();
        $second = $service->locations();

        $this->assertCount(7, $first);
        $this->assertSame($first, $second);
        $this->assertSame('Berwick', $first[0]['name']);
        $this->assertSame('Bishop Auckland', $first[4]['name']);
        $this->assertSame('Middlesbrough', $first[5]['name']);
        $this->assertTrue(Cache::has(HomeWeatherService::CACHE_KEY));

        Http::assertSentCount(1);
    }

    public function test_home_weather_service_does_not_cache_failed_responses(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response('Unavailable', 503),
        ]);

        $locations = app(HomeWeatherService::class)->locations();

        $this->assertSame([], $locations);
        $this->assertFalse(Cache::has(HomeWeatherService::CACHE_KEY));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function openMeteoPayload(): array
    {
        $rows = [];

        foreach (HomeWeatherService::LOCATIONS as $index => $place) {
            $rows[] = [
                'latitude' => $place['latitude'],
                'longitude' => $place['longitude'],
                'location_id' => $index,
                'current' => [
                    'time' => '2026-08-11T23:15',
                    'temperature_2m' => 16.4,
                    'weather_code' => 3,
                    'wind_speed_10m' => 8.2,
                ],
                'daily' => [
                    'time' => ['2026-08-11', '2026-08-12'],
                    'weather_code' => [3, 80],
                    'temperature_2m_max' => [20.1, 18.4],
                    'temperature_2m_min' => [10.0, 11.2],
                ],
            ];
        }

        return $rows;
    }
}
