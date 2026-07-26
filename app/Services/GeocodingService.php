<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeocodingService
{
    /**
     * Search UK places by postcode, place name, or "lat,lng".
     *
     * @return list<array{display_name: string, latitude: float, longitude: float, address: string}>
     */
    public function search(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $query, $matches)) {
            $lat = (float) $matches[1];
            $lng = (float) $matches[2];

            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $reverse = $this->reverse($lat, $lng);

                return [[
                    'display_name' => $reverse['display_name'] ?: "{$lat}, {$lng}",
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'address' => $reverse['address'],
                ]];
            }
        }

        $response = Http::withHeaders($this->headers())
            ->timeout(8)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 5,
                'countrycodes' => 'gb',
            ]);

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json() ?? [])
            ->map(function (array $item) {
                $lat = (float) ($item['lat'] ?? 0);
                $lng = (float) ($item['lon'] ?? 0);

                return [
                    'display_name' => (string) ($item['display_name'] ?? ''),
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'address' => $this->formatAddress($item['address'] ?? [], (string) ($item['display_name'] ?? '')),
                ];
            })
            ->filter(fn (array $item) => $item['display_name'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array{display_name: string, address: string}
     */
    public function reverse(float $latitude, float $longitude): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(8)
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'addressdetails' => 1,
                'zoom' => 18,
            ]);

        if (! $response->successful()) {
            return ['display_name' => '', 'address' => ''];
        }

        $payload = $response->json() ?? [];

        return [
            'display_name' => (string) ($payload['display_name'] ?? ''),
            'address' => $this->formatAddress($payload['address'] ?? [], (string) ($payload['display_name'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    private function formatAddress(array $parts, string $fallback): string
    {
        $line = collect([
            $parts['house_number'] ?? null,
            $parts['road'] ?? $parts['pedestrian'] ?? $parts['path'] ?? null,
            $parts['suburb'] ?? $parts['village'] ?? $parts['hamlet'] ?? $parts['neighbourhood'] ?? null,
            $parts['town'] ?? $parts['city'] ?? $parts['municipality'] ?? null,
            $parts['county'] ?? null,
            $parts['postcode'] ?? null,
        ])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->implode(', ');

        return $line !== '' ? $line : Str::of($fallback)->before(', United Kingdom')->toString();
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'User-Agent' => config('app.name', 'NE Coarse Fishing').'/1.0 (venue-wizard)',
            'Accept' => 'application/json',
        ];
    }
}
