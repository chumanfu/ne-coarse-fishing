<?php

namespace App\Models;

use Database\Factories\SiteAnnouncementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteAnnouncement extends Model
{
    /** @use HasFactory<SiteAnnouncementFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'level',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCurrentlyVisible(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now)
            ->orderByDesc('starts_at');
    }

    public function levelLabel(): string
    {
        return match ($this->level) {
            'warning' => 'Warning',
            'maintenance' => 'Maintenance',
            default => 'Notice',
        };
    }
}
