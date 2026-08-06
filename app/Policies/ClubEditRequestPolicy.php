<?php

namespace App\Policies;

use App\Models\ClubEditRequest;
use App\Models\User;

class ClubEditRequestPolicy
{
    public function view(User $user, ClubEditRequest $request): bool
    {
        return $user->hasRole('super_admin') || $request->user_id === $user->id;
    }

    public function review(User $user, ClubEditRequest $request): bool
    {
        return $user->hasRole('super_admin') && $request->isPending();
    }
}
