<?php

namespace App\Models;

use App\Mail\VenueClaimInvite;
use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory;

    /** @var array<string, string> */
    public const FACILITIES = [
        'wifi' => 'WiFi',
        'camping' => 'Camping',
        'touring_vehicles' => 'Touring vehicles',
        'no_commercial_vehicles' => 'Commercial vehicles not allowed',
        'lodges' => 'Lodges',
        'toilets' => 'Toilets',
        'showers' => 'Showers',
        'tackle_shop' => 'Tackle shop',
        'fishery_pellets_only' => 'Fishery pellets only',
        'food' => 'Food',
        'drink' => 'Drink',
        'car_park' => 'Car park',
        'park_at_peg' => 'Park at peg',
    ];

    protected $fillable = [
        'user_id',
        'manager_id',
        'name',
        'slug',
        'overview',
        'latitude',
        'longitude',
        'address',
        'contact_email',
        'invite_sent_at',
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
        'facilities',
        'is_complex',
        'is_approved',
        'manager_verified',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'facilities' => 'array',
            'is_complex' => 'boolean',
            'is_approved' => 'boolean',
            'manager_verified' => 'boolean',
            'invite_sent_at' => 'datetime',
        ];
    }

    public function markInviteSent(): void
    {
        $this->forceFill(['invite_sent_at' => now()])->save();
    }

    public function sendClaimInvite(): bool
    {
        if (blank($this->contact_email)) {
            return false;
        }

        Mail::to($this->contact_email, $this->name)
            ->send(new VenueClaimInvite($this));

        $this->markInviteSent();

        return true;
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

        if ($this->manager_id === $user->id) {
            return true;
        }

        // Club owners can manage venues linked to clubs they manage.
        return $user->hasRole('club_owner')
            && $this->clubs()->where('manager_id', $user->id)->exists();
    }

    public function canManagePegs(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Venue manager, original submitter, or owner of a linked club.
        return $this->manager_id === $user->id
            || $this->user_id === $user->id
            || ($user->hasRole('club_owner') && $this->clubs()->where('manager_id', $user->id)->exists());
    }

    public function hasFacility(string $facility): bool
    {
        return in_array($facility, $this->facilities ?? [], true);
    }

    /**
     * @return list<string>
     */
    public function facilityLabels(): array
    {
        return collect($this->facilities ?? [])
            ->map(fn (string $facility) => self::FACILITIES[$facility] ?? ucfirst(str_replace('_', ' ', $facility)))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>|null  $facilities
     * @return list<string>
     */
    public static function normalizeFacilities(?array $facilities): array
    {
        return collect($facilities ?? [])
            ->filter(fn ($facility) => is_string($facility) && array_key_exists($facility, self::FACILITIES))
            ->unique()
            ->values()
            ->all();
    }
}
