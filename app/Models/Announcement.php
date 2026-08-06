<?php

namespace App\Models;

use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'user_id',
        'type',
        'title',
        'body',
        'published_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'ends_at' => 'datetime',
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

    public function scopeCurrentlyVisible($query)
    {
        $now = now();

        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->where(function ($inner) use ($now) {
                $inner->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            });
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'stocking' => 'Stocking Update',
            default => 'Announcement',
        };
    }
}
