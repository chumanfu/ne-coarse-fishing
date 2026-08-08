<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterVideo extends Model
{
    protected $fillable = [
        'water_id',
        'user_id',
        'youtube_url',
        'youtube_id',
        'title',
        'is_approved',
        'approved_by',
        'approved_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function water(): BelongsTo
    {
        return $this->belongsTo(Water::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    public function markApproved(?User $approver = null): void
    {
        $this->forceFill([
            'is_approved' => true,
            'approved_by' => $approver?->id,
            'approved_at' => now(),
        ])->save();
    }

    public function embedUrl(): string
    {
        return 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id;
    }

    public function thumbnailUrl(): string
    {
        return 'https://i.ytimg.com/vi/'.$this->youtube_id.'/hqdefault.jpg';
    }

    public function watchUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->youtube_id;
    }

    public static function extractYoutubeId(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $url = trim($url);

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url) === 1) {
            return $url;
        }

        $patterns = [
            '/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/watch\?.*\bv=([A-Za-z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }
}
