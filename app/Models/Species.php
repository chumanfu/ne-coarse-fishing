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

    protected $fillable = [
        'name',
        'slug',
    ];

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
}
