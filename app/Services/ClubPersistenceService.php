<?php

namespace App\Services;

use App\Models\Club;
use App\Support\Uploads;
use Illuminate\Http\UploadedFile;

class ClubPersistenceService
{
    /** @var list<string> */
    public const EDITABLE_FIELDS = [
        'name',
        'url',
        'facebook_url',
        'overview',
        'town',
        'address',
        'phone',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function apply(Club $club, array $data, ?UploadedFile $logo = null): Club
    {
        $payload = collect($data)
            ->only(self::EDITABLE_FIELDS)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();

        if (isset($payload['name']) && $payload['name'] !== $club->name) {
            $payload['slug'] = Club::uniqueSlug((string) $payload['name'], $club->id);
        }

        if ($logo) {
            if (filled($club->logo_path) && ! Uploads::isStockPath($club->logo_path)) {
                Uploads::delete($club->logo_path);
            }
            $payload['logo_path'] = Uploads::store($logo, 'club-logos');
        }

        $club->update($payload);

        return $club->fresh();
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function proposedFromInput(array $input): array
    {
        return collect($input)
            ->only(self::EDITABLE_FIELDS)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();
    }
}
