<?php

namespace App\Models;

use Database\Factories\WaterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Water extends Model
{
    /** @use HasFactory<WaterFactory> */
    use HasFactory;

    /** @var array<string, string> */
    public const FACILITIES = [
        'wifi' => 'WiFi',
        'camping' => 'Camping',
        'touring_vehicles' => 'Touring vehicles',
        'no_commercial_vehicles' => 'Commercial vehicles not allowed',
        'lodges' => 'Lodges',
        'toilets' => 'Toilets',
        'showers' => 'Showers',
        'tackle_shop' => 'Tackle shop',
        'fishery_pellets_only' => 'Fishery pellets only',
        'food' => 'Food',
        'drink' => 'Drink',
        'car_park' => 'Car park',
        'park_at_peg' => 'Park at peg',
    ];

    protected $fillable = [
        'venue_id',
        'name',
        'description',
        'peg_count',
        'depth_info',
        'facilities',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'water_species');
    }

    public function fishingSessions(): HasMany
    {
        return $this->hasMany(FishingSession::class);
    }

    public function pegs(): HasMany
    {
        return $this->hasMany(WaterPeg::class)->orderBy('sort_order')->orderBy('id');
    }

    public function hasFacility(string $facility): bool
    {
        return in_array($facility, $this->facilities ?? [], true);
    }

    /**
     * @return list<string>
     */
    public function facilityLabels(): array
    {
        return collect($this->facilities ?? [])
            ->map(fn (string $facility) => self::FACILITIES[$facility] ?? ucfirst(str_replace('_', ' ', $facility)))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>|null  $facilities
     * @return list<string>
     */
    public static function normalizeFacilities(?array $facilities): array
    {
        return collect($facilities ?? [])
            ->filter(fn ($facility) => is_string($facility) && array_key_exists($facility, self::FACILITIES))
            ->unique()
            ->values()
            ->all();
    }
}
