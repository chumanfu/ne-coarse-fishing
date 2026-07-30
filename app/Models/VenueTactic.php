<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueTactic extends Model
{
    protected $fillable = [
        'venue_id',
        'user_id',
        'fishing_session_id',
        'water_id',
        'peg_number',
        'body',
        'fished_at',
    ];

    protected function casts(): array
    {
        return [
            'fished_at' => 'date',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fishingSession(): BelongsTo
    {
        return $this->belongsTo(FishingSession::class);
    }

    public function water(): BelongsTo
    {
        return $this->belongsTo(Water::class);
    }
}
