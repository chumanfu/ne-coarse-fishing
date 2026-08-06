<?php

namespace App\Models;

use Database\Factories\TackleShopEditRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TackleShopEditRequest extends Model
{
    /** @use HasFactory<TackleShopEditRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'tackle_shop_id',
        'user_id',
        'message',
        'proposed_data',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'proposed_data' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function tackleShop(): BelongsTo
    {
        return $this->belongsTo(TackleShop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
