<?php

namespace App\Models;

use Database\Factories\MatchReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchReport extends Model
{
    /** @use HasFactory<MatchReportFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'water_id',
        'user_id',
        'title',
        'body',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function water(): BelongsTo
    {
        return $this->belongsTo(Water::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
