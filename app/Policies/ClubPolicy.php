<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Club $club): bool
    {
        return $club->is_published || ($user && ($club->isManagedBy($user) || $user->hasRole('super_admin')));
    }

    public function manage(User $user, Club $club): bool
    {
        return $user->hasRole('super_admin')
            || ($user->hasRole('club_owner') && $club->isManagedBy($user));
    }

    public function update(User $user, Club $club): bool
    {
        return $this->manage($user, $club);
    }

    public function claim(User $user, Club $club): bool
    {
        return $club->is_published
            && $club->manager_id !== $user->id
            && ! $club->claims()->where('user_id', $user->id)->where('status', 'pending')->exists();
    }

    public function suggestEdit(User $user, Club $club): bool
    {
        if (! $club->is_published || $this->manage($user, $club)) {
            return false;
        }

        return ! $club->editRequests()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }
}
