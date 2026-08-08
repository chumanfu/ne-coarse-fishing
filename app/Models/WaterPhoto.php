<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Uploads;

class WaterPhoto extends Model
{
    protected $fillable = [
        'water_id',
        'user_id',
        'image_path',
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

    protected static function booted(): void
    {
        static::deleting(function (WaterPhoto $photo): void {
            Uploads::delete($photo->image_path);
        });
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

    public function url(): string
    {
        return Uploads::url($this->image_path);
    }
}
