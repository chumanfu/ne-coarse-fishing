<?php

namespace App\Services;

use App\Models\TackleShopEditRequest;

class TackleShopEditRequestComparison
{
    /** @var array<string, string> */
    public const LABELS = [
        'name' => 'Name',
        'url' => 'Website',
        'overview' => 'Overview',
        'town' => 'Town',
        'address' => 'Address',
        'phone' => 'Phone',
        'location_type' => 'Location type',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'logo_path' => 'Logo path',
    ];

    /**
     * @return list<array{field: string, label: string, before: mixed, after: mixed, changed: bool}>
     */
    public function build(TackleShopEditRequest $request): array
    {
        $shop = $request->tackleShop;
        $proposed = $request->proposed_data ?? [];
        $fields = [];

        foreach (self::LABELS as $field => $label) {
            $before = $shop->{$field} ?? null;
            $after = array_key_exists($field, $proposed) ? $proposed[$field] : $before;
            $fields[] = [
                'field' => $field,
                'label' => $label,
                'before' => $before,
                'after' => $after,
                'changed' => (string) $before !== (string) $after,
            ];
        }

        return $fields;
    }
}
