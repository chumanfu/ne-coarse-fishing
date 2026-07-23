<?php

namespace App\Models;

use Database\Factories\SessionCatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionCatch extends Model
{
    /** @use HasFactory<SessionCatchFactory> */
    use HasFactory;

    protected $fillable = [
        'fishing_session_id',
        'species_id',
        'weight_lb',
        'bait',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'weight_lb' => 'float',
        ];
    }

    public function fishingSession(): BelongsTo
    {
        return $this->belongsTo(FishingSession::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
