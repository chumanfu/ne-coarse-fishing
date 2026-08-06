<?php

namespace App\Models;

use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'manager_id',
        'name',
        'slug',
        'overview',
        'latitude',
        'longitude',
        'address',
        'url',
        'facebook_url',
        'what3words',
        'directions',
        'day_ticket_info',
        'membership_info',
        'ticket_type',
        'opening_times',
        'season_info',
        'tactics_guide',
        'is_complex',
        'is_approved',
        'manager_verified',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_complex' => 'boolean',
            'is_approved' => 'boolean',
            'manager_verified' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Venue $venue): void {
            if (blank($venue->slug)) {
                $venue->slug = static::uniqueSlug($venue->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'venue';
        $slug = $base;
        $i = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function waters(): HasMany
    {
        return $this->hasMany(Water::class)->orderBy('sort_order')->orderBy('name');
    }

    public function fishingSessions(): HasMany
    {
        return $this->hasMany(FishingSession::class);
    }

    public function matchReports(): HasMany
    {
        return $this->hasMany(MatchReport::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(VenueClaim::class);
    }

    public function editRequests(): HasMany
    {
        return $this->hasMany(VenueEditRequest::class);
    }

    public function anglerTactics(): HasMany
    {
        return $this->hasMany(VenueTactic::class)->latest('fished_at')->latest('created_at');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VenuePhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function favouritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favourite_venues')->withTimestamps();
    }

    public function isFavouritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasFavourited($this);
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class)->withTimestamps();
    }

    public static function normalizeWhat3words(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/^\/+/', '', $normalized) ?? $normalized;

        return $normalized !== '' ? $normalized : null;
    }

    public function what3wordsLabel(): ?string
    {
        if (blank($this->what3words)) {
            return null;
        }

        return '///'.$this->what3words;
    }

    public function what3wordsUrl(): ?string
    {
        if (blank($this->what3words)) {
            return null;
        }

        return 'https://what3words.com/'.$this->what3words;
    }

    public function allSpecies()
    {
        return Species::query()
            ->whereHas('waters', fn ($q) => $q->where('venue_id', $this->id))
            ->orderBy('name')
            ->get();
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function ticketTypeLabel(): string
    {
        return match ($this->ticket_type) {
            'day_ticket' => 'Day Ticket',
            'club' => 'Club Waters',
            'syndicate' => 'Syndicate',
            'mixed' => 'Day Ticket & Club',
            default => ucfirst(str_replace('_', ' ', $this->ticket_type)),
        };
    }

    public function isManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $this->manager_id === $user->id;
    }

    public function canManagePegs(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Venue manager (typically fishery_manager role after a claim) or original submitter.
        return $this->manager_id === $user->id || $this->user_id === $user->id;
    }
}
