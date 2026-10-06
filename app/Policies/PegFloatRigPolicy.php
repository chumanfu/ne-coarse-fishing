<?php

namespace App\Policies;

use App\Models\PegFloatRig;
use App\Models\User;

class PegFloatRigPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, PegFloatRig $rig): bool
    {
        return $rig->user_id === $user->id || $user->hasRole('super_admin');
    }
}
