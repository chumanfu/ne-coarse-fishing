<?php

namespace App\Support;

/**
 * Shot weights, olivette sizes and pattern blurbs. The calculator reads the same
 * JSON file in the browser, so the guide can never drift from the engine.
 */
final class ShotReference
{
    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    /** @return list<array{size: string, label: string, grams: float, use: string}> */
    public static function shots(): array
    {
        return self::data()['shots'];
    }

    /** @return list<float> */
    public static function olivettes(): array
    {
        return self::data()['olivettes'];
    }

    /** @return list<string> */
    public static function poleSizes(): array
    {
        return self::data()['poleSizes'];
    }

    /** Shot capacity for a pole float size. Listed sizes use their usual commercial weight. */
    public static function poleGrams(string $size): float
    {
        $key = strtolower((string) preg_replace('/\s+/', '', $size));
        foreach (self::data()['poleWeights'] ?? [] as $rated => $grams) {
            if (strtolower((string) $rated) === $key) {
                return (float) $grams;
            }
        }

        $second = explode('x', $key)[1] ?? null;

        return is_numeric($second) ? ((float) $second) / 100 : 0.0;
    }

    /** @return list<array{name: string, text: string}> */
    public static function patterns(): array
    {
        return self::data()['patterns'];
    }

    public static function formatGrams(float $grams): string
    {
        if ($grams >= 10) {
            return round($grams, 1).'g';
        }

        return number_format(round($grams, 2), 2).'g';
    }

    /** @return array<string, mixed> */
    private static function data(): array
    {
        return self::$data ??= json_decode(
            (string) file_get_contents(resource_path('data/shot-reference.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
