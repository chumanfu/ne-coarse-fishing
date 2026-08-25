<?php

namespace App\Support;

class WaterGeometry
{
    /** Approximate Great Britain bounding box for MVP sanity checks. */
    public const GB_MIN_LAT = 49.5;

    public const GB_MAX_LAT = 61.0;

    public const GB_MIN_LNG = -8.5;

    public const GB_MAX_LNG = 2.0;

    /**
     * Normalize a GeoJSON LineString geometry (coordinates as [lng, lat]).
     *
     * @param  array<string, mixed>|null  $geometry
     * @return array{type: string, coordinates: list<array{0: float, 1: float}>}|null
     */
    public static function normalize(?array $geometry): ?array
    {
        if ($geometry === null || ($geometry['type'] ?? null) !== 'LineString') {
            return null;
        }

        $coordinates = $geometry['coordinates'] ?? null;

        if (! is_array($coordinates) || count($coordinates) < 2) {
            return null;
        }

        $normalized = [];

        foreach ($coordinates as $pair) {
            if (! is_array($pair) || count($pair) < 2) {
                return null;
            }

            $lng = round((float) $pair[0], 7);
            $lat = round((float) $pair[1], 7);

            if (! self::coordinateInGb($lat, $lng)) {
                return null;
            }

            $normalized[] = [$lng, $lat];
        }

        if (count($normalized) < 2) {
            return null;
        }

        return [
            'type' => 'LineString',
            'coordinates' => $normalized,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $geometry
     */
    public static function isValid(?array $geometry): bool
    {
        return self::normalize($geometry) !== null;
    }

    public static function coordinateInGb(float $lat, float $lng): bool
    {
        return $lat >= self::GB_MIN_LAT
            && $lat <= self::GB_MAX_LAT
            && $lng >= self::GB_MIN_LNG
            && $lng <= self::GB_MAX_LNG;
    }
}
