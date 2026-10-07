<?php

namespace App\Support;

use App\Models\PegFloatRig;

/**
 * Pole-float templates. One of each shotting pattern for every usual pole size.
 * They are shared, and a logged-in angler copies one to make a rig of their own.
 */
final class SystemRigs
{
    /** @var array<int, string> */
    private const TITLES = [
        1 => 'Bulk and droppers',
        2 => 'Positive Single Bulk',
        3 => 'Spread Bulk',
        4 => 'Strung Out Slow Fall',
        5 => 'Over-depth Double Bulk',
    ];

    /** @var array<int, string> */
    private const NOTES = [
        1 => 'The bulk gets the rig down quickly, and the two droppers let the hook bait fall more slowly. The shot is spaced about six inches apart, with a six inch hook length, which also helps keep the rig from tangling.',
        2 => 'A positive pattern for fishing dead depth. The single bulk gets the bait down and holds it on the deck, including when small fish are higher in the swim.',
        3 => 'The shot is spaced about two inches apart, starting just above the hook length. Still positive, and the float can show a bite before it has fully settled. A useful pellet pattern.',
        4 => 'The shot is spread down the rig so the hook bait falls slowly and evenly. Watch the float as each shot settles. An early settle often means a fish has taken the bait on the drop.',
        5 => 'Set over-depth. The upper bulk cocks the float as normal, and the lower bulk sits near the hook. When a fish lifts the bait, that lower bulk rises and the float tip lifts.',
    ];

    public static function install(): void
    {
        foreach (self::definitions() as $definition) {
            PegFloatRig::query()->updateOrCreate(
                ['system_key' => $definition['system_key']],
                $definition,
            );
        }
    }

    public static function blurb(string $size): string
    {
        return match ($size) {
            '4x10' => 'About 0.10g. Shallow water, up to 2.5 feet.',
            '4x12' => 'About 0.20g. Around 2.5 to 4 feet.',
            '4x14' => 'About 0.40g. Around 4 to 6 feet.',
            '4x16' => 'About 0.50g. Deeper water, or a steady day.',
            '4x18' => 'About 0.75g. Deeper water, up to 2.5m.',
            '4x20' => 'About 1.00g. Heavy shot, or deeper water.',
            default => '',
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function definitions(): array
    {
        $rigs = [];

        foreach (self::floats() as $size => $float) {
            foreach (self::patterns($size, $float) as $number => $pattern) {
                $olivette = self::olivetteGrams($pattern['placements']);

                $rigs[] = [
                    'system_key' => "pole-{$size}-rig-{$number}",
                    'name' => "{$size} · Rig {$number} – ".self::TITLES[$number],
                    'float_name' => 'Pole float',
                    'float_size' => $size,
                    'float_type' => 'pole',
                    'float_grams' => $float['grams'],
                    'depth' => $pattern['depth'],
                    'depth_unit' => $pattern['unit'],
                    'pattern_id' => $olivette ? 'olivette' : $pattern['pattern_id'],
                    'olivette_grams' => $olivette,
                    'notes' => self::NOTES[$number],
                    'placements' => $pattern['placements'],
                    'is_system' => true,
                    'user_id' => null,
                    'water_peg_id' => null,
                ];
            }
        }

        return $rigs;
    }

    /**
     * @return array<string, array{grams: float, depth: float, unit: string}>
     */
    private static function floats(): array
    {
        return [
            '4x10' => ['grams' => 0.10, 'depth' => 2.5, 'unit' => 'ft'],
            '4x12' => ['grams' => 0.20, 'depth' => 4, 'unit' => 'ft'],
            '4x14' => ['grams' => 0.40, 'depth' => 5, 'unit' => 'ft'],
            '4x16' => ['grams' => 0.50, 'depth' => 8, 'unit' => 'ft'],
            '4x18' => ['grams' => 0.75, 'depth' => 2.5, 'unit' => 'm'],
            '4x20' => ['grams' => 1.00, 'depth' => 10, 'unit' => 'ft'],
        ];
    }

    /**
     * @param  array{grams: float, depth: float, unit: string}  $float
     * @return array<int, array{pattern_id: string, depth: float, unit: string, placements: list<array<string, mixed>>}>
     */
    private static function patterns(string $size, array $float): array
    {
        $over = $float['unit'] === 'm'
            ? ['depth' => round($float['depth'] + 0.3, 2), 'unit' => 'm']
            : ['depth' => $float['depth'] + 1, 'unit' => 'ft'];

        return [
            1 => [
                'pattern_id' => 'bulk_droppers',
                'depth' => $float['depth'],
                'unit' => $float['unit'],
                'placements' => self::bulkAndDroppers($size),
            ],
            2 => [
                'pattern_id' => 'bulk_droppers',
                'depth' => $float['depth'],
                'unit' => $float['unit'],
                'placements' => self::singleBulk($size),
            ],
            3 => [
                'pattern_id' => 'bulk_droppers',
                'depth' => $float['depth'],
                'unit' => $float['unit'],
                'placements' => self::spreadBulk($size),
            ],
            4 => [
                'pattern_id' => 'strung',
                'depth' => $float['depth'],
                'unit' => $float['unit'],
                'placements' => self::strungOut($size, $float),
            ],
            5 => [
                'pattern_id' => 'bulk_droppers',
                'depth' => $over['depth'],
                'unit' => $over['unit'],
                'placements' => self::doubleBulk($size),
            ],
        ];
    }

    /**
     * Bulk at 18in, droppers at 12in and 6in.
     *
     * @return list<array<string, mixed>>
     */
    private static function bulkAndDroppers(string $size): array
    {
        return match ($size) {
            '4x10' => [
                self::place('bulk', 18, [['No10', 1]]),
                self::place('dropper', 12, [['No11', 1]]),
                self::place('dropper', 6, [['No12', 1]]),
            ],
            '4x12' => [
                self::place('bulk', 18, [['No10', 3]]),
                self::place('dropper', 12, [['No10', 1]]),
                self::place('dropper', 6, [['No10', 1]]),
            ],
            '4x14' => [
                self::place('bulk', 18, [['No8', 4], ['No13', 1]]),
                self::place('dropper', 12, [['No8', 1]]),
                self::place('dropper', 6, [['No8', 1]]),
            ],
            '4x16' => [
                self::place('bulk', 18, [['No8', 6]]),
                self::place('dropper', 12, [['No8', 1]]),
                self::place('dropper', 6, [['No8', 1]]),
            ],
            '4x18' => [
                self::place('olivette', 18, [], 0.6),
                self::place('dropper', 12, [['No10', 2]]),
                self::place('dropper', 6, [['No10', 1], ['No12', 1]]),
            ],
            '4x20' => [
                self::place('olivette', 18, [], 0.8),
                self::place('dropper', 12, [['No10', 3]]),
                self::place('dropper', 6, [['No10', 2]]),
            ],
        };
    }

    /**
     * Everything in one bulk, four inches from the hook.
     *
     * @return list<array<string, mixed>>
     */
    private static function singleBulk(string $size): array
    {
        return match ($size) {
            '4x10' => [self::place('bulk', 4, [['No10', 1], ['No11', 1], ['No12', 1]])],
            '4x12' => [self::place('bulk', 4, [['No10', 5]])],
            '4x14' => [self::place('bulk', 4, [['No8', 6], ['No13', 1]])],
            '4x16' => [self::place('bulk', 4, [['No8', 8]])],
            '4x18' => [self::place('olivette', 4, [['No10', 3], ['No12', 1]], 0.6)],
            '4x20' => [self::place('olivette', 4, [['No10', 5]], 0.8)],
        };
    }

    /**
     * Individual shot, two inches apart, starting just above a short hook length.
     *
     * @return list<array<string, mixed>>
     */
    private static function spreadBulk(string $size): array
    {
        return match ($size) {
            '4x10' => self::singles('bulk', ['No12', 'No11', 'No10'], 5, 2),
            '4x12' => self::singles('bulk', array_fill(0, 5, 'No10'), 5, 2),
            '4x14' => self::singles('bulk', [...array_fill(0, 6, 'No8'), 'No13'], 5, 2),
            '4x16' => self::singles('bulk', array_fill(0, 8, 'No8'), 5, 2),
            '4x18' => self::singles('bulk', array_fill(0, 12, 'No8'), 5, 2),
            '4x20' => self::singles('bulk', array_fill(0, 5, 'No4'), 5, 2),
        };
    }

    /**
     * The same shot spread from the hook length up towards the float.
     *
     * @param  array{grams: float, depth: float, unit: string}  $float
     * @return list<array<string, mixed>>
     */
    private static function strungOut(string $size, array $float): array
    {
        $top = round(self::depthInches($float) - 12, 1);

        $sizes = match ($size) {
            '4x10' => ['No12', 'No11', 'No10'],
            '4x12' => array_fill(0, 5, 'No10'),
            '4x14' => [...array_fill(0, 6, 'No8'), 'No13'],
            '4x16' => array_fill(0, 8, 'No8'),
            '4x18' => array_fill(0, 12, 'No8'),
            '4x20' => ['No9', ...array_fill(0, 15, 'No8')],
        };

        return self::singles('strung', $sizes, 6, null, $top);
    }

    /**
     * Upper bulk where a normal rig would cock the float, lower bulk by the hook.
     *
     * @return list<array<string, mixed>>
     */
    private static function doubleBulk(string $size): array
    {
        return match ($size) {
            '4x10' => [
                self::place('bulk', 18, [['No10', 1], ['No11', 1]], note: 'Sets the float'),
                self::place('bulk', 6, [['No12', 1]], note: 'Near the hook, for the lift bite'),
            ],
            '4x12' => [
                self::place('bulk', 18, [['No10', 3]], note: 'Sets the float'),
                self::place('bulk', 6, [['No10', 2]], note: 'Near the hook, for the lift bite'),
            ],
            '4x14' => [
                self::place('bulk', 18, [['No8', 4], ['No13', 1]], note: 'Sets the float'),
                self::place('bulk', 6, [['No8', 2]], note: 'Near the hook, for the lift bite'),
            ],
            '4x16' => [
                self::place('bulk', 18, [['No8', 5]], note: 'Sets the float'),
                self::place('bulk', 6, [['No8', 3]], note: 'Near the hook, for the lift bite'),
            ],
            '4x18' => [
                self::place('olivette', 18, [], 0.6, 'Sets the float'),
                self::place('bulk', 6, [['No10', 3], ['No12', 1]], note: 'Near the hook, for the lift bite'),
            ],
            '4x20' => [
                self::place('olivette', 18, [], 0.8, 'Sets the float'),
                self::place('bulk', 6, [['No10', 5]], note: 'Near the hook, for the lift bite'),
            ],
        };
    }

    /**
     * @param  list<string>  $sizes  Nearest the hook first.
     * @return list<array<string, mixed>>
     */
    private static function singles(string $role, array $sizes, float $start, ?float $step, ?float $end = null): array
    {
        $inches = $step === null
            ? self::spaced(count($sizes), $start, (float) $end)
            : array_map(
                fn (int $index) => round($start + ($index * $step), 1),
                array_keys($sizes),
            );

        $groups = [];
        foreach ($sizes as $index => $size) {
            $groups[] = self::place($role, $inches[$index], [[$size, 1]]);
        }

        return $groups;
    }

    /**
     * @return list<float>
     */
    private static function spaced(int $count, float $from, float $to): array
    {
        if ($count <= 1) {
            return [$from];
        }

        $step = ($to - $from) / ($count - 1);

        return array_map(
            fn (int $index) => round($from + ($index * $step), 1),
            range(0, $count - 1),
        );
    }

    /**
     * @param  list<array{0: string, 1: int}>  $shot
     * @return array<string, mixed>
     */
    private static function place(string $role, float $inches, array $shot, ?float $olivette = null, ?string $note = null): array
    {
        return [
            'role' => $role,
            'height_cm' => round($inches * 2.54, 2),
            'olivette_grams' => $olivette,
            'anchor' => 'hook',
            'distance_unit' => 'in',
            'note' => $note,
            'items' => array_map(
                fn (array $item) => ['size' => $item[0], 'count' => $item[1]],
                $shot,
            ),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $placements
     */
    private static function olivetteGrams(array $placements): ?float
    {
        foreach ($placements as $group) {
            if ($group['olivette_grams']) {
                return (float) $group['olivette_grams'];
            }
        }

        return null;
    }

    /**
     * @param  array{grams: float, depth: float, unit: string}  $float
     */
    private static function depthInches(array $float): float
    {
        return $float['unit'] === 'm'
            ? $float['depth'] * 100 / 2.54
            : $float['depth'] * 12;
    }
}
