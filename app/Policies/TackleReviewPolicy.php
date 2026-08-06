<?php

namespace App\Policies;

use App\Models\TackleReview;
use App\Models\User;

class TackleReviewPolicy
{
    public function view(?User $user, TackleReview $review): bool
    {
        return $review->is_published || ($user && $this->update($user, $review));
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TackleReview $review): bool
    {
        return $review->user_id === $user->id || $user->hasRole('super_admin');
    }

    public function delete(User $user, TackleReview $review): bool
    {
        return $this->update($user, $review);
    }
}
