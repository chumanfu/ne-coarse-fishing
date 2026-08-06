<?php

namespace App\Policies;

use App\Models\TackleShopEditRequest;
use App\Models\User;

class TackleShopEditRequestPolicy
{
    public function view(User $user, TackleShopEditRequest $request): bool
    {
        return $user->hasRole('super_admin') || $request->user_id === $user->id;
    }

    public function review(User $user, TackleShopEditRequest $request): bool
    {
        return $user->hasRole('super_admin') && $request->isPending();
    }
}
