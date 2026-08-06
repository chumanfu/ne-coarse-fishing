<?php

namespace App\Policies;

use App\Models\TackleShop;
use App\Models\User;

class TackleShopPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, TackleShop $tackleShop): bool
    {
        return $tackleShop->is_published || ($user && ($tackleShop->isManagedBy($user) || $user->hasRole('super_admin')));
    }

    public function manage(User $user, TackleShop $tackleShop): bool
    {
        return $tackleShop->isManagedBy($user) || $user->hasRole('super_admin');
    }

    public function update(User $user, TackleShop $tackleShop): bool
    {
        return $this->manage($user, $tackleShop);
    }

    public function claim(User $user, TackleShop $tackleShop): bool
    {
        return $tackleShop->is_published
            && $tackleShop->manager_id !== $user->id
            && ! $tackleShop->claims()->where('user_id', $user->id)->where('status', 'pending')->exists();
    }

    public function suggestEdit(User $user, TackleShop $tackleShop): bool
    {
        if (! $tackleShop->is_published || $tackleShop->isManagedBy($user) || $user->hasRole('super_admin')) {
            return false;
        }

        return ! $tackleShop->editRequests()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }
}
