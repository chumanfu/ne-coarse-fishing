<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venue;

class VenuePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Venue $venue): bool
    {
        return $venue->is_approved || ($user && ($venue->user_id === $user->id || $user->hasRole('super_admin')));
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Venue $venue): bool
    {
        return $user->hasRole('super_admin') || $venue->isManagedBy($user);
    }

    public function manage(User $user, Venue $venue): bool
    {
        return $venue->isManagedBy($user);
    }

    public function delete(User $user, Venue $venue): bool
    {
        return $user->hasRole('super_admin');
    }

    public function claim(User $user, Venue $venue): bool
    {
        return $venue->is_approved
            && $venue->manager_id !== $user->id
            && ! $venue->claims()->where('user_id', $user->id)->where('status', 'pending')->exists();
    }

    public function suggestEdit(User $user, Venue $venue): bool
    {
        if (! $venue->is_approved || $venue->isManagedBy($user) || $user->hasRole('super_admin')) {
            return false;
        }

        return ! $venue->editRequests()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }
}
