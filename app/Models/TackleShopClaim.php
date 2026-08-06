<?php

namespace App\Models;

use Database\Factories\TackleShopClaimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TackleShopClaim extends Model
{
    /** @use HasFactory<TackleShopClaimFactory> */
    use HasFactory;

    protected $fillable = [
        'tackle_shop_id',
        'user_id',
        'message',
        'status',
    ];

    public function tackleShop(): BelongsTo
    {
        return $this->belongsTo(TackleShop::class);
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
