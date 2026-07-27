<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VenueTactic;

class VenueTacticPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, VenueTactic $tactic): bool
    {
        return $tactic->user_id === $user->id || $user->hasRole('super_admin');
    }

    public function delete(User $user, VenueTactic $tactic): bool
    {
        return $tactic->user_id === $user->id || $user->hasRole('super_admin');
    }
}
