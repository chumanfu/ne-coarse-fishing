<?php

namespace App\Models;

use Database\Factories\ClubClaimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubClaim extends Model
{
    /** @use HasFactory<ClubClaimFactory> */
    use HasFactory;

    protected $fillable = [
        'club_id',
        'user_id',
        'message',
        'status',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
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
