<?php

namespace App\Services;

use App\Models\Species;
use App\Models\Venue;
use App\Models\VenueEditRequest;
use App\Models\Water;
use Illuminate\Support\Collection;

class VenueEditRequestComparison
{
    /** @var array<string, string> */
    private const FIELD_LABELS = [
        'name' => 'Name',
        'overview' => 'Overview',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'address' => 'Address',
        'url' => 'Website',
        'what3words' => 'what3words',
        'directions' => 'Directions / parking',
        'day_ticket_info' => 'Day tickets',
        'membership_info' => 'Membership',
        'ticket_type' => 'Ticket type',
        'opening_times' => 'Opening times',
        'season_info' => 'Seasonal restrictions',
        'is_complex' => 'Complex venue',
    ];

    /** @return array{fields: list<array{label: string, before: string, after: string, changed: bool}>, waters: list<array{label: string, before: string, after: string, changed: bool, status: string}>} */
    public function build(VenueEditRequest $request): array
    {
        $request->loadMissing(['venue.waters.species']);

        $venue = $request->venue;
        $proposedVenue = $request->proposed_data['venue'] ?? [];
        $proposedWaters = $request->proposed_data['waters'] ?? [];

        $fields = collect(VenuePersistenceService::VENUE_FIELDS)
            ->map(function (string $key) use ($venue, $proposedVenue): array {
                $before = $this->formatVenueValue($key, data_get($venue, $key));
                $after = $this->formatVenueValue($key, $proposedVenue[$key] ?? data_get($venue, $key));

                return [
                    'label' => self::FIELD_LABELS[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                    'before' => $before,
                    'after' => $after,
                    'changed' => $before !== $after,
                ];
            })
            ->values()
            ->all();

        return [
            'fields' => $fields,
            'waters' => $this->compareWaters($venue, $proposedWaters),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $proposedWaters
     * @return list<array{label: string, before: string, after: string, changed: bool, status: string}>
     */
    private function compareWaters(Venue $venue, array $proposedWaters): array
    {
        /** @var Collection<int, Water> $beforeById */
        $beforeById = $venue->waters->keyBy('id');
        $usedBeforeIds = [];
        $rows = [];

        foreach ($proposedWaters as $index => $proposed) {
            $beforeWater = ! empty($proposed['id']) ? $beforeById->get((int) $proposed['id']) : null;

            if ($beforeWater) {
                $usedBeforeIds[] = $beforeWater->id;
            }

            $label = $proposed['name'] ?? $beforeWater?->name ?? 'Water '.($index + 1);
            $before = $beforeWater ? $this->formatWater($beforeWater) : '—';
            $after = $this->formatProposedWater($proposed);
            $changed = $beforeWater
                ? $this->waterHasChanges($beforeWater, $proposed)
                : true;
            $status = $beforeWater ? ($changed ? 'modified' : 'unchanged') : 'added';

            $rows[] = [
                'label' => $label,
                'before' => $before,
                'after' => $after,
                'changed' => $changed,
                'status' => $status,
            ];
        }

        foreach ($beforeById as $beforeWater) {
            if (in_array($beforeWater->id, $usedBeforeIds, true)) {
                continue;
            }

            $rows[] = [
                'label' => $beforeWater->name,
                'before' => $this->formatWater($beforeWater),
                'after' => '—',
                'changed' => true,
                'status' => 'removed',
            ];
        }

        return $rows;
    }

    /** @param  array<string, mixed>  $proposed */
    private function waterHasChanges(Water $beforeWater, array $proposed): bool
    {
        $normalize = static fn (?string $value): ?string => filled($value) ? trim($value) : null;

        if ($normalize($proposed['name'] ?? null) !== $beforeWater->name) {
            return true;
        }

        if ($normalize($proposed['description'] ?? null) !== $normalize($beforeWater->description)) {
            return true;
        }

        $beforePegs = $beforeWater->peg_count !== null ? (string) $beforeWater->peg_count : null;
        $afterPegs = ($proposed['peg_count'] ?? '') !== '' ? (string) $proposed['peg_count'] : null;
        if ($afterPegs !== $beforePegs) {
            return true;
        }

        if ($normalize($proposed['depth_info'] ?? null) !== $normalize($beforeWater->depth_info)) {
            return true;
        }

        $beforeSpeciesIds = $beforeWater->species->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $afterSpeciesIds = collect($proposed['species'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        return $beforeSpeciesIds !== $afterSpeciesIds;
    }

    /** @return list<string> */
    private function sortedSpeciesNames(Water $water): array
    {
        return $water->species->pluck('name')->sort()->values()->all();
    }

    /** @param  array<string, mixed>  $proposed */
    private function sortedProposedSpeciesNames(array $proposed): array
    {
        return Species::query()
            ->whereIn('id', collect($proposed['species'] ?? [])->filter()->map(fn ($id) => (int) $id))
            ->pluck('name')
            ->sort()
            ->values()
            ->all();
    }

    private function formatSpeciesLine(array $names): ?string
    {
        return $names !== [] ? 'Species: '.implode(', ', $names) : null;
    }

    private function formatVenueValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($key) {
            'ticket_type' => match ($value) {
                'day_ticket' => 'Day ticket',
                'club' => 'Club',
                'syndicate' => 'Syndicate',
                'mixed' => 'Mixed',
                default => (string) $value,
            },
            'is_complex' => $value ? 'Yes' : 'No',
            'what3words' => blank($value) ? '—' : '///'.Venue::normalizeWhat3words((string) $value),
            default => (string) $value,
        };
    }

    private function formatWater(Water $water): string
    {
        $lines = array_filter([
            'Name: '.$water->name,
            filled($water->description) ? 'Description: '.$water->description : null,
            filled($water->peg_count) ? 'Pegs: '.$water->peg_count : null,
            filled($water->depth_info) ? 'Depth: '.$water->depth_info : null,
            $this->formatSpeciesLine($this->sortedSpeciesNames($water)),
        ]);

        return implode("\n", $lines);
    }

    /** @param  array<string, mixed>  $proposed */
    private function formatProposedWater(array $proposed): string
    {
        $lines = array_filter([
            'Name: '.($proposed['name'] ?? '—'),
            filled($proposed['description'] ?? null) ? 'Description: '.$proposed['description'] : null,
            filled($proposed['peg_count'] ?? null) ? 'Pegs: '.$proposed['peg_count'] : null,
            filled($proposed['depth_info'] ?? null) ? 'Depth: '.$proposed['depth_info'] : null,
            $this->formatSpeciesLine($this->sortedProposedSpeciesNames($proposed)),
        ]);

        return implode("\n", $lines);
    }
}
