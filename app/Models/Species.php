<?php

namespace App\Models;

use Database\Factories\SpeciesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Species extends Model
{
    /** @use HasFactory<SpeciesFactory> */
    use HasFactory;

    public const TYPES = [
        'cyprinid' => 'Cyprinid (carp family)',
        'predator' => 'Predator',
        'game' => 'Game',
        'eel' => 'Eel / lamprey',
        'minor' => 'Minor species',
    ];

    public const HABITATS = [
        'river' => 'River',
        'stream' => 'Stream',
        'canal' => 'Canal',
        'lake' => 'Lake',
        'pond' => 'Pond',
        'reservoir' => 'Reservoir',
        'drain' => 'Drain',
    ];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'habitats',
    ];

    protected function casts(): array
    {
        return [
            'habitats' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Species $species): void {
            if (blank($species->slug)) {
                $species->slug = Str::slug($species->name);
            }
        });
    }

    public function waters(): BelongsToMany
    {
        return $this->belongsToMany(Water::class, 'water_species');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    /**
     * @return list<string>
     */
    public function habitatLabels(): array
    {
        return collect($this->habitats ?? [])
            ->map(fn (string $habitat) => self::HABITATS[$habitat] ?? ucfirst($habitat))
            ->values()
            ->all();
    }

    public function foundIn(string $habitat): bool
    {
        return in_array($habitat, $this->habitats ?? [], true);
    }
}
