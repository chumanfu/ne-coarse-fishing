<?php

namespace App\Services;

use App\Models\Venue;
use App\Models\Water;
use Illuminate\Support\Facades\DB;

class VenuePersistenceService
{
    /** @var list<string> */
    public const VENUE_FIELDS = [
        'name',
        'overview',
        'latitude',
        'longitude',
        'address',
        'url',
        'what3words',
        'directions',
        'day_ticket_info',
        'membership_info',
        'ticket_type',
        'opening_times',
        'season_info',
        'is_complex',
    ];

    /**
     * @param  array{venue: array<string, mixed>, waters: list<array<string, mixed>>}  $data
     */
    public function apply(Venue $venue, array $data): Venue
    {
        return DB::transaction(function () use ($venue, $data) {
            $venuePayload = collect($data['venue'] ?? [])
                ->only(self::VENUE_FIELDS)
                ->all();

            if (isset($venuePayload['name'])) {
                $venuePayload['slug'] = Venue::uniqueSlug($venuePayload['name'], $venue->id);
            }

            if (isset($venuePayload['is_complex']) === false && isset($data['waters'])) {
                $venuePayload['is_complex'] = count($data['waters']) > 1;
            }

            $venue->update($venuePayload);

            if (! isset($data['waters'])) {
                return $venue->fresh(['waters.species']);
            }

            $keepIds = [];

            foreach ($data['waters'] as $index => $waterData) {
                $water = null;

                if (! empty($waterData['id'])) {
                    $water = $venue->waters()->whereKey($waterData['id'])->first();
                }

                $attributes = [
                    'name' => $waterData['name'],
                    'description' => filled($waterData['description'] ?? null) ? $waterData['description'] : null,
                    'peg_count' => ($waterData['peg_count'] ?? '') !== '' ? $waterData['peg_count'] : null,
                    'depth_info' => filled($waterData['depth_info'] ?? null) ? $waterData['depth_info'] : null,
                    'sort_order' => $index,
                ];

                if ($water) {
                    $water->update($attributes);
                } else {
                    $water = $venue->waters()->create($attributes);
                }

                $speciesIds = collect($waterData['species'] ?? [])
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $water->species()->sync($speciesIds);
                $keepIds[] = $water->id;
            }

            $venue->waters()->whereNotIn('id', $keepIds)->each(function (Water $water) {
                $water->species()->detach();
                $water->delete();
            });

            return $venue->fresh(['waters.species']);
        });
    }
}
