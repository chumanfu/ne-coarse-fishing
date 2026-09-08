<?php

namespace App\Services;

use App\Models\SessionCatch;
use App\Models\WaterPeg;
use App\Support\Weight;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PegCatchStatsService
{
    /**
     * Build map/popup payloads for the given pegs, including all-time catch stats.
     *
     * @param  Collection<int, WaterPeg>|iterable<WaterPeg>  $pegs
     * @return list<array{
     *     id: int,
     *     label: string,
     *     description: ?string,
     *     x: float,
     *     y: float,
     *     photos: list<array{url: string}>,
     *     session_count: int,
     *     fish_caught: int,
     *     heaviest_g: ?int,
     *     heaviest_label: ?string,
     *     top_species: list<array{name: string, total: int}>
     * }>
     */
    public function mapPayloads(iterable $pegs, ?string $unit = null): array
    {
        $unit = Weight::normaliseUnit($unit);
        $pegs = Collection::make($pegs)->values();

        if ($pegs->isEmpty()) {
            return [];
        }

        $pegIds = $pegs->pluck('id')->map(fn ($id) => (int) $id)->all();

        $totals = SessionCatch::query()
            ->select([
                'fishing_sessions.water_peg_id',
                DB::raw('COALESCE(SUM(session_catches.quantity), 0) as fish_caught'),
                // Only single-fish entries count, otherwise a big match bag
                // would masquerade as one enormous fish.
                DB::raw("MAX(CASE WHEN session_catches.entry_type = 'individual' THEN session_catches.weight_g END) as heaviest_g"),
            ])
            ->join('fishing_sessions', 'fishing_sessions.id', '=', 'session_catches.fishing_session_id')
            ->whereIn('fishing_sessions.water_peg_id', $pegIds)
            ->groupBy('fishing_sessions.water_peg_id')
            ->get()
            ->keyBy('water_peg_id');

        $sessionCounts = WaterPeg::query()
            ->whereIn('id', $pegIds)
            ->withCount('fishingSessions')
            ->get()
            ->pluck('fishing_sessions_count', 'id');

        $speciesRows = SessionCatch::query()
            ->select([
                'fishing_sessions.water_peg_id',
                'species.name',
                DB::raw('SUM(session_catches.quantity) as total'),
            ])
            ->join('fishing_sessions', 'fishing_sessions.id', '=', 'session_catches.fishing_session_id')
            ->join('species', 'species.id', '=', 'session_catches.species_id')
            ->whereIn('fishing_sessions.water_peg_id', $pegIds)
            ->groupBy('fishing_sessions.water_peg_id', 'species.id', 'species.name')
            ->orderByDesc('total')
            ->get()
            ->groupBy('water_peg_id');

        return $pegs->map(function (WaterPeg $peg) use ($totals, $sessionCounts, $speciesRows, $unit) {
            $stats = $totals->get($peg->id);
            $heaviest = Weight::fromGrams($stats->heaviest_g ?? null);
            $topSpecies = ($speciesRows->get($peg->id) ?? collect())
                ->take(5)
                ->map(fn ($row) => [
                    'name' => (string) $row->name,
                    'total' => (int) $row->total,
                ])
                ->values()
                ->all();

            return [
                'id' => (int) $peg->id,
                'water_id' => (int) $peg->water_id,
                'label' => $peg->label(),
                'description' => filled($peg->description) ? (string) $peg->description : null,
                'x' => (float) $peg->map_x,
                'y' => (float) $peg->map_y,
                'photos' => $peg->photos
                    ->map(fn ($photo) => ['url' => $photo->url()])
                    ->values()
                    ->all(),
                'session_count' => (int) ($sessionCounts[$peg->id] ?? 0),
                'fish_caught' => (int) ($stats->fish_caught ?? 0),
                'heaviest_g' => $heaviest?->grams,
                'heaviest_label' => $heaviest?->format($unit),
                'top_species' => $topSpecies,
            ];
        })->all();
    }
}
