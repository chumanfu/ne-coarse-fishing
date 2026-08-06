<?php

namespace App\Services;

use App\Models\TackleShop;
use App\Support\Uploads;
use Illuminate\Http\UploadedFile;

class TackleShopPersistenceService
{
    /** @var list<string> */
    public const EDITABLE_FIELDS = [
        'name',
        'url',
        'overview',
        'town',
        'address',
        'phone',
        'location_type',
        'latitude',
        'longitude',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function apply(TackleShop $shop, array $data, ?UploadedFile $logo = null): TackleShop
    {
        $payload = collect($data)
            ->only(self::EDITABLE_FIELDS)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();

        if (array_key_exists('latitude', $payload)) {
            $payload['latitude'] = $payload['latitude'] !== null ? (float) $payload['latitude'] : null;
        }
        if (array_key_exists('longitude', $payload)) {
            $payload['longitude'] = $payload['longitude'] !== null ? (float) $payload['longitude'] : null;
        }

        if (isset($payload['name']) && $payload['name'] !== $shop->name) {
            $payload['slug'] = TackleShop::uniqueSlug((string) $payload['name'], $shop->id);
        }

        if ($logo) {
            if (filled($shop->logo_path) && ! Uploads::isStockPath($shop->logo_path)) {
                Uploads::delete($shop->logo_path);
            }
            $payload['logo_path'] = Uploads::store($logo, 'tackle-shop-logos');
        }

        $shop->update($payload);

        return $shop->fresh();
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function proposedFromInput(array $input): array
    {
        $payload = collect($input)
            ->only(self::EDITABLE_FIELDS)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();

        if (array_key_exists('latitude', $payload)) {
            $payload['latitude'] = $payload['latitude'] !== null ? (float) $payload['latitude'] : null;
        }
        if (array_key_exists('longitude', $payload)) {
            $payload['longitude'] = $payload['longitude'] !== null ? (float) $payload['longitude'] : null;
        }

        return $payload;
    }
}
