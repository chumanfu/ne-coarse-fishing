<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VenueEditRequest;

class VenueEditRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('fishery_manager');
    }

    public function view(User $user, VenueEditRequest $request): bool
    {
        return $user->hasRole('super_admin')
            || $request->venue->isManagedBy($user)
            || $request->user_id === $user->id;
    }

    public function review(User $user, VenueEditRequest $request): bool
    {
        return $request->isPending() && (
            $user->hasRole('super_admin') || $request->venue->isManagedBy($user)
        );
    }
}
