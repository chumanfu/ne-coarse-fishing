<?php

namespace App\Services;

use App\Models\ClubEditRequest;

class ClubEditRequestComparison
{
    /** @var array<string, string> */
    public const LABELS = [
        'name' => 'Name',
        'url' => 'Website',
        'facebook_url' => 'Facebook',
        'overview' => 'Overview',
        'town' => 'Town',
        'address' => 'Address',
        'phone' => 'Phone',
        'logo_path' => 'Logo path',
    ];

    /**
     * @return list<array{field: string, label: string, before: mixed, after: mixed, changed: bool}>
     */
    public function build(ClubEditRequest $request): array
    {
        $club = $request->club;
        $proposed = $request->proposed_data ?? [];
        $fields = [];

        foreach (self::LABELS as $field => $label) {
            $before = $club->{$field} ?? null;
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
