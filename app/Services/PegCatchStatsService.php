<?php

namespace App\Services;

use App\Models\SessionCatch;
use App\Models\WaterPeg;
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
     *     heaviest_lb: ?float,
     *     top_species: list<array{name: string, total: int}>
     * }>
     */
    public function mapPayloads(iterable $pegs): array
    {
        $pegs = Collection::make($pegs)->values();

        if ($pegs->isEmpty()) {
            return [];
        }

        $pegIds = $pegs->pluck('id')->map(fn ($id) => (int) $id)->all();

        $totals = SessionCatch::query()
            ->select([
                'fishing_sessions.water_peg_id',
                DB::raw('COALESCE(SUM(session_catches.quantity), 0) as fish_caught'),
                DB::raw('MAX(session_catches.weight_lb) as heaviest_lb'),
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

        return $pegs->map(function (WaterPeg $peg) use ($totals, $sessionCounts, $speciesRows) {
            $stats = $totals->get($peg->id);
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
                'heaviest_lb' => $stats?->heaviest_lb !== null ? (float) $stats->heaviest_lb : null,
                'top_species' => $topSpecies,
            ];
        })->all();
    }
}
