<?php

namespace App\Models;

use Database\Factories\WaterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Uploads;

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
        'map_image_path',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Water $water): void {
            if (filled($water->map_image_path)) {
                Uploads::delete($water->map_image_path);
            }

            $water->photos()->each(fn (WaterPhoto $photo) => $photo->delete());
            $water->videos()->each(fn (WaterVideo $video) => $video->delete());
        });
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

    public function photos(): HasMany
    {
        return $this->hasMany(WaterPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(WaterVideo::class)->orderBy('sort_order')->orderBy('id');
    }

    public function mapImageUrl(): ?string
    {
        return filled($this->map_image_path) ? Uploads::url($this->map_image_path) : null;
    }

    public function hasMapImage(): bool
    {
        return filled($this->map_image_path);
    }
}
