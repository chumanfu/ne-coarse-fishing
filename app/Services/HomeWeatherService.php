<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class HomeWeatherService
{
    public const CACHE_KEY = 'home.weather.locations';

    public const CACHE_TTL_SECONDS = 2700;

    /**
     * Fixed bankside towns shown on the home page (north → south).
     *
     * @var list<array{id: string, name: string, latitude: float, longitude: float}>
     */
    public const LOCATIONS = [
        ['id' => 'berwick', 'name' => 'Berwick', 'latitude' => 55.7708, 'longitude' => -2.0074],
        ['id' => 'morpeth', 'name' => 'Morpeth', 'latitude' => 55.1675, 'longitude' => -1.6908],
        ['id' => 'newcastle', 'name' => 'Newcastle', 'latitude' => 54.9783, 'longitude' => -1.6178],
        ['id' => 'sunderland', 'name' => 'Sunderland', 'latitude' => 54.9069, 'longitude' => -1.3838],
        ['id' => 'bishop-auckland', 'name' => 'Bishop Auckland', 'latitude' => 54.6566, 'longitude' => -1.6770],
        ['id' => 'middlesbrough', 'name' => 'Middlesbrough', 'latitude' => 54.5742, 'longitude' => -1.2350],
        ['id' => 'thirsk', 'name' => 'Thirsk', 'latitude' => 54.2327, 'longitude' => -1.3424],
    ];

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     temperature_c: int,
     *     condition: string,
     *     icon: string,
     *     wind_mph: int,
     *     outlook: string|null
     * }>
     */
    public function locations(): array
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        $locations = $this->fetch();

        if ($locations !== []) {
            Cache::put(self::CACHE_KEY, $locations, self::CACHE_TTL_SECONDS);
        }

        return $locations;
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     temperature_c: int,
     *     condition: string,
     *     icon: string,
     *     wind_mph: int,
     *     outlook: string|null
     * }>
     */
    protected function fetch(): array
    {
        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => implode(',', array_column(self::LOCATIONS, 'latitude')),
                    'longitude' => implode(',', array_column(self::LOCATIONS, 'longitude')),
                    'current' => 'temperature_2m,weather_code,wind_speed_10m',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min',
                    'timezone' => 'Europe/London',
                    'forecast_days' => 2,
                    'wind_speed_unit' => 'mph',
                ]);

            if (! $response->successful()) {
                return [];
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                return [];
            }

            // Multi-location responses are a list; a single location is one object.
            $rows = array_is_list($payload) ? $payload : [$payload];

            $locations = [];

            foreach (self::LOCATIONS as $index => $place) {
                $row = $rows[$index] ?? null;

                if (! is_array($row) || ! isset($row['current']) || ! is_array($row['current'])) {
                    continue;
                }

                $current = $row['current'];
                $code = (int) ($current['weather_code'] ?? -1);
                $condition = $this->conditionLabel($code);

                if ($condition === null || ! isset($current['temperature_2m'])) {
                    continue;
                }

                $locations[] = [
                    'id' => $place['id'],
                    'name' => $place['name'],
                    'temperature_c' => (int) round((float) $current['temperature_2m']),
                    'condition' => $condition,
                    'icon' => $this->iconForCode($code),
                    'wind_mph' => (int) round((float) ($current['wind_speed_10m'] ?? 0)),
                    'outlook' => $this->outlookSummary($row['daily'] ?? null),
                ];
            }

            return $locations;
        } catch (Throwable $exception) {
            Log::warning('Home weather fetch failed.', [
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array{time?: list<string>, weather_code?: list<int|float>, temperature_2m_max?: list<int|float>, temperature_2m_min?: list<int|float>}|null  $daily
     */
    protected function outlookSummary(?array $daily): ?string
    {
        if ($daily === null) {
            return null;
        }

        $codes = $daily['weather_code'] ?? [];
        $maxTemps = $daily['temperature_2m_max'] ?? [];
        $minTemps = $daily['temperature_2m_min'] ?? [];

        // Prefer tomorrow when present; otherwise today's daily summary.
        $index = isset($codes[1]) ? 1 : 0;

        if (! isset($codes[$index], $maxTemps[$index], $minTemps[$index])) {
            return null;
        }

        $label = $this->conditionLabel((int) $codes[$index]);

        if ($label === null) {
            return null;
        }

        $prefix = $index === 1 ? 'Tomorrow' : 'Today';
        $high = (int) round((float) $maxTemps[$index]);
        $low = (int) round((float) $minTemps[$index]);

        return "{$prefix}: {$label}, {$low}–{$high}°C";
    }

    protected function conditionLabel(int $code): ?string
    {
        return match (true) {
            $code === 0 => 'Clear',
            $code === 1 => 'Mainly clear',
            $code === 2 => 'Partly cloudy',
            $code === 3 => 'Overcast',
            in_array($code, [45, 48], true) => 'Fog',
            $code >= 51 && $code <= 67 => 'Rain',
            $code >= 71 && $code <= 77 => 'Snow',
            $code >= 80 && $code <= 82 => 'Showers',
            $code >= 85 && $code <= 86 => 'Snow showers',
            $code >= 95 && $code <= 99 => 'Thunder',
            default => null,
        };
    }

    protected function iconForCode(int $code): string
    {
        return match (true) {
            $code === 0, $code === 1 => 'sun',
            $code === 2 => 'sun-cloud',
            $code === 3, in_array($code, [45, 48], true) => 'cloud',
            $code >= 51 && $code <= 67, $code >= 80 && $code <= 82 => 'rain',
            $code >= 71 && $code <= 77, $code >= 85 && $code <= 86 => 'snow',
            $code >= 95 && $code <= 99 => 'thunder',
            default => 'cloud',
        };
    }
}
