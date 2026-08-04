<?php

namespace App\Models;

use Database\Factories\WaterPegFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaterPeg extends Model
{
    /** @use HasFactory<WaterPegFactory> */
    use HasFactory;

    protected $fillable = [
        'water_id',
        'created_by',
        'name',
        'number',
        'description',
        'latitude',
        'longitude',
        'is_verified',
        'verified_by',
        'verified_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function water(): BelongsTo
    {
        return $this->belongsTo(Water::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function fishingSessions(): HasMany
    {
        return $this->hasMany(FishingSession::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(WaterPegPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::deleting(function (WaterPeg $peg): void {
            $peg->photos()->each(fn (WaterPegPhoto $photo) => $photo->delete());
        });
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return $query->where(function (Builder $inner) use ($user) {
            $inner->where('is_verified', true);

            if ($user) {
                $inner->orWhere('created_by', $user->id);
            }
        });
    }

    public function label(): string
    {
        $parts = array_filter([$this->number, $this->name], fn ($part) => filled($part));

        if ($parts === []) {
            return 'Peg #'.$this->id;
        }

        return implode(' · ', $parts);
    }

    public function markVerified(?User $verifier = null): void
    {
        $this->forceFill([
            'is_verified' => true,
            'verified_by' => $verifier?->id,
            'verified_at' => now(),
        ])->save();
    }
}
