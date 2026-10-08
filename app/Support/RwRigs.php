<?php

namespace App\Support;

use App\Models\PegFloatRig;

/**
 * RW Floats' published shotting. Blank lines on their guide are left out.
 * A logged-in angler copies one to make a rig of their own.
 */
final class RwRigs
{
    /** @var list<string> */
    private const GROUPS = [
        '1.2mm Winter Maggie',
        '1.5mm Maggie',
        '1.7mm Maggie',
        'Dibber',
        'Muddie',
        '1.5mm Dink',
        'Dobber',
        'Shalla Slim',
    ];

    /** @var list<string> */
    private const SIZES = [
        '4x8', '4x10', '4x12', '4x14', '4x16',
        'No 1', 'No 2',
        '0.4g',
        '3x10', '5x10',
        'No -1', '2x13', '3x12',
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

    public static function sortKey(PegFloatRig $rig): string
    {
        $group = array_search($rig->float_name, self::GROUPS, true);
        $size = array_search($rig->float_size, self::SIZES, true);

        return sprintf('%02d-%02d', $group === false ? 99 : $group, $size === false ? 99 : $size);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function definitions(): array
    {
        $rigs = [];

        foreach (self::lines() as $line) {
            $placements = self::shot($line['droppers'], $line['bulk'], $line['trimmers']);
            $notes = self::describe($line['droppers'], $line['bulk'], $line['trimmers']);
            if ($line['note'] !== null) {
                $notes .= ' '.$line['note'];
            }

            $rigs[] = [
                'system_key' => $line['key'],
                'name' => $line['float'].' · '.$line['size'],
                'float_name' => $line['float'],
                'float_size' => $line['size'],
                'float_type' => $line['type'],
                'float_grams' => self::grams($placements),
                'depth' => $line['depth'],
                'depth_unit' => 'ft',
                'pattern_id' => 'bulk_droppers',
                'olivette_grams' => null,
                'notes' => $notes,
                'placements' => $placements,
                'is_system' => true,
                'catalogue' => 'rw',
                'use_stored_grams' => true,
                'user_id' => null,
                'water_peg_id' => null,
            ];
        }

        return $rigs;
    }

    /**
     * Shot RW actually lists. Droppers are separate shot, nearest the hook first.
     *
     * @return list<array{key: string, float: string, size: string, type: string, depth: float, droppers: list<array{0: string, 1: int}>, bulk: list<array{0: string, 1: int}>, trimmers: list<array{0: string, 1: int}>, note: ?string}>
     */
    private static function lines(): array
    {
        return [
            self::line('rw-winter-maggie-4x8', '1.2mm Winter Maggie', '4x8', 2, bulk: [['No12', 5]]),
            self::line('rw-winter-maggie-4x10', '1.2mm Winter Maggie', '4x10', 2.5, droppers: [['No11', 1]], bulk: [['No10', 4]], trimmers: [['No13', 2]]),
            self::line('rw-winter-maggie-4x12', '1.2mm Winter Maggie', '4x12', 4, droppers: [['No11', 1], ['No11', 1]], bulk: [['No9', 5]], trimmers: [['No13', 3]]),
            self::line('rw-winter-maggie-4x14', '1.2mm Winter Maggie', '4x14', 5, droppers: [['No11', 1], ['No11', 1]], bulk: [['No8', 6], ['No12', 1]], trimmers: [['No13', 3]]),
            self::line('rw-winter-maggie-4x16', '1.2mm Winter Maggie', '4x16', 8, droppers: [['No11', 1], ['No11', 1]], bulk: [['No8', 7], ['No11', 1]], trimmers: [['No13', 2]]),

            self::line('rw-maggie-15-4x10', '1.5mm Maggie', '4x10', 2.5, droppers: [['No11', 1], ['No11', 1]], bulk: [['No10', 4]]),
            self::line('rw-maggie-15-4x12', '1.5mm Maggie', '4x12', 4, droppers: [['No11', 1], ['No11', 1]], bulk: [['No9', 5], ['No11', 1]]),
            self::line('rw-maggie-15-4x14', '1.5mm Maggie', '4x14', 5, droppers: [['No11', 1], ['No11', 1]], bulk: [['No8', 6], ['No11', 1]]),
            self::line('rw-maggie-15-4x16', '1.5mm Maggie', '4x16', 8, droppers: [['No11', 1], ['No11', 1]], bulk: [['No8', 7], ['No11', 1]]),

            self::line('rw-maggie-17-4x10', '1.7mm Maggie', '4x10', 2.5, bulk: [['No10', 5]]),
            self::line('rw-maggie-17-4x12', '1.7mm Maggie', '4x12', 4, bulk: [['No9', 6]]),
            self::line('rw-maggie-17-4x14', '1.7mm Maggie', '4x14', 5, bulk: [['No8', 7]]),
            self::line('rw-maggie-17-4x16', '1.7mm Maggie', '4x16', 8, bulk: [['No8', 8]]),

            self::line('rw-dibber-1', 'Dibber', 'No 1', 2, 'dibber', bulk: [['No11', 2]]),
            self::line('rw-dibber-2', 'Dibber', 'No 2', 2, 'dibber', bulk: [['No10', 2]]),

            self::line('rw-muddie-04', 'Muddie', '0.4g', 5, bulk: [['No8', 6]]),

            self::line('rw-dink-4x12', '1.5mm Dink', '4x12', 4, bulk: [['No9', 5], ['No11', 1]]),
            self::line('rw-dink-4x14', '1.5mm Dink', '4x14', 5, bulk: [['No9', 8]]),
            self::line('rw-dink-4x16', '1.5mm Dink', '4x16', 8, bulk: [['No8', 8]]),

            self::line('rw-dobber-3x10', 'Dobber', '3x10', 2.5, droppers: [['No12', 1], ['No12', 1]], bulk: [['No11', 2], ['No13', 1]]),
            self::line('rw-dobber-5x10', 'Dobber', '5x10', 2.5, droppers: [['No12', 1], ['No12', 1]], bulk: [['No10', 3], ['No11', 1], ['No12', 1]]),

            self::line('rw-shalla-no-1', 'Shalla Slim', 'No -1', 2, bulk: [['No10', 4]], trimmers: [['No13', 1]], note: 'RW does not name the trimmer size, so this uses one No.13.'),
            self::line('rw-shalla-2x13', 'Shalla Slim', '2x13', 2, bulk: [['No13', 2]]),
            self::line('rw-shalla-3x12', 'Shalla Slim', '3x12', 2, bulk: [['No12', 3]]),
        ];
    }

    /**
     * @param  list<array{0: string, 1: int}>  $droppers
     * @param  list<array{0: string, 1: int}>  $bulk
     * @param  list<array{0: string, 1: int}>  $trimmers
     * @return array{key: string, float: string, size: string, type: string, depth: float, droppers: list<array{0: string, 1: int}>, bulk: list<array{0: string, 1: int}>, trimmers: list<array{0: string, 1: int}>, note: ?string}
     */
    private static function line(
        string $key,
        string $float,
        string $size,
        float $depth,
        string $type = 'pole',
        array $droppers = [],
        array $bulk = [],
        array $trimmers = [],
        ?string $note = null,
    ): array {
        return compact('key', 'float', 'size', 'type', 'depth', 'droppers', 'bulk', 'trimmers', 'note');
    }

    /**
     * Two droppers sit at 6in and 12in, the bulk at 18in, and trimmers between them.
     * A bulk on its own sits 6in from the hook. A trimmer under that bulk sits at 8in, with the bulk at 12in.
     *
     * @param  list<array{0: string, 1: int}>  $droppers
     * @param  list<array{0: string, 1: int}>  $bulk
     * @param  list<array{0: string, 1: int}>  $trimmers
     * @return list<array<string, mixed>>
     */
    private static function shot(array $droppers, array $bulk, array $trimmers): array
    {
        $placements = [];

        foreach ($droppers as $index => $shot) {
            $placements[] = self::place('dropper', 6 + ($index * 6), [$shot]);
        }

        if ($droppers !== [] && $bulk !== []) {
            $highestDropper = 6 + ((count($droppers) - 1) * 6);
            if ($trimmers !== []) {
                $placements[] = self::place('trim', round((18 + $highestDropper) / 2, 1), $trimmers);
            }
            $placements[] = self::place('bulk', 18, $bulk);

            return $placements;
        }

        if ($trimmers !== [] && $bulk !== []) {
            $placements[] = self::place('trim', 8, $trimmers);
            $placements[] = self::place('bulk', 12, $bulk);

            return $placements;
        }

        if ($bulk !== []) {
            $placements[] = self::place('bulk', 6, $bulk);
        }

        return $placements;
    }

    /**
     * @param  list<array{0: string, 1: int}>  $droppers
     * @param  list<array{0: string, 1: int}>  $bulk
     * @param  list<array{0: string, 1: int}>  $trimmers
     */
    private static function describe(array $droppers, array $bulk, array $trimmers): string
    {
        $parts = [];

        if ($droppers !== []) {
            $parts[] = (count($droppers) === 1 ? 'Dropper: ' : 'Droppers: ').self::phrase($droppers);
        }

        if ($bulk !== []) {
            $parts[] = 'Bulk: '.self::phrase($bulk);
        }

        if ($trimmers !== []) {
            $parts[] = 'Trimmers: '.self::phrase($trimmers);
        }

        return implode('. ', $parts).'.';
    }

    /**
     * @param  list<array{0: string, 1: int}>  $groups
     */
    private static function phrase(array $groups): string
    {
        $counts = [];
        foreach ($groups as [$size, $count]) {
            $counts[$size] = ($counts[$size] ?? 0) + $count;
        }

        $labels = collect(ShotReference::shots())->pluck('label', 'size');

        return collect($counts)
            ->map(fn (int $count, string $size) => $count.' × '.($labels[$size] ?? $size))
            ->implode(' and ');
    }

    /**
     * @param  list<array<string, mixed>>  $placements
     */
    private static function grams(array $placements): float
    {
        $weights = collect(ShotReference::shots())->pluck('grams', 'size');
        $total = 0.0;

        foreach ($placements as $group) {
            foreach ($group['items'] as $item) {
                $total += $weights[$item['size']] * $item['count'];
            }
        }

        return round($total, 3);
    }

    /**
     * @param  list<array{0: string, 1: int}>  $shot
     * @return array<string, mixed>
     */
    private static function place(string $role, float $inches, array $shot): array
    {
        return [
            'role' => $role,
            'height_cm' => round($inches * 2.54, 2),
            'olivette_grams' => null,
            'anchor' => 'hook',
            'distance_unit' => 'in',
            'note' => null,
            'items' => array_map(
                fn (array $item) => ['size' => $item[0], 'count' => $item[1]],
                $shot,
            ),
        ];
    }
}
