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

    protected $fillable = [
        'venue_id',
        'name',
        'description',
        'peg_count',
        'depth_info',
        'sort_order',
    ];

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
}
