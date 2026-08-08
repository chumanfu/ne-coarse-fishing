<?php

namespace App\Models;

use Database\Factories\FishingSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingSession extends Model
{
    /** @use HasFactory<FishingSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'venue_id',
        'water_id',
        'water_peg_id',
        'fished_at',
        'duration_hours',
        'weather',
        'peg_number',
        'peg_latitude',
        'peg_longitude',
        'commentary',
        'tactics_tip',
    ];

    protected function casts(): array
    {
        return [
            'fished_at' => 'date',
            'peg_latitude' => 'float',
            'peg_longitude' => 'float',
        ];
    }

    public function hasPegLocation(): bool
    {
        if ($this->water_peg_id) {
            return true;
        }

        return $this->waterPeg?->hasMapPosition()
            || ($this->peg_latitude !== null && $this->peg_longitude !== null);
    }

    public function pegLabel(): ?string
    {
        if ($this->waterPeg) {
            return $this->waterPeg->label();
        }

        return $this->peg_number;
    }

    public function pegMapX(): ?float
    {
        return $this->waterPeg?->map_x;
    }

    public function pegMapY(): ?float
    {
        return $this->waterPeg?->map_y;
    }

    public function pegMapLatitude(): ?float
    {
        return $this->waterPeg?->latitude ?? $this->peg_latitude;
    }

    public function pegMapLongitude(): ?float
    {
        return $this->waterPeg?->longitude ?? $this->peg_longitude;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function water(): BelongsTo
    {
        return $this->belongsTo(Water::class);
    }

    public function waterPeg(): BelongsTo
    {
        return $this->belongsTo(WaterPeg::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SessionPhoto::class);
    }

    public function catches(): HasMany
    {
        return $this->hasMany(SessionCatch::class);
    }

    public function venueTactic(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(VenueTactic::class);
    }
}
