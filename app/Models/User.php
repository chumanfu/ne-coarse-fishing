<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super_admin') || $this->hasRole('fishery_manager');
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class)->withTimestamps();
    }

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    public function managedVenues(): HasMany
    {
        return $this->hasMany(Venue::class, 'manager_id');
    }

    public function fishingSessions(): HasMany
    {
        return $this->hasMany(FishingSession::class);
    }

    public function venueClaims(): HasMany
    {
        return $this->hasMany(VenueClaim::class);
    }

    public function venueEditRequests(): HasMany
    {
        return $this->hasMany(VenueEditRequest::class);
    }

    public function venueTactics(): HasMany
    {
        return $this->hasMany(VenueTactic::class);
    }

    public function favouriteVenues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class, 'favourite_venues')->withTimestamps();
    }

    public function hasFavourited(Venue $venue): bool
    {
        if ($this->relationLoaded('favouriteVenues')) {
            return $this->favouriteVenues->contains($venue);
        }

        return $this->favouriteVenues()->whereKey($venue->id)->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
