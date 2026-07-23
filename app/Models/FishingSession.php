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
        'fished_at',
        'duration_hours',
        'weather',
        'peg_number',
        'commentary',
    ];

    protected function casts(): array
    {
        return [
            'fished_at' => 'date',
        ];
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

    public function photos(): HasMany
    {
        return $this->hasMany(SessionPhoto::class);
    }

    public function catches(): HasMany
    {
        return $this->hasMany(SessionCatch::class);
    }
}
