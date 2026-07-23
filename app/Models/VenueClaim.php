<?php

namespace App\Models;

use Database\Factories\VenueClaimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueClaim extends Model
{
    /** @use HasFactory<VenueClaimFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'user_id',
        'message',
        'status',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
