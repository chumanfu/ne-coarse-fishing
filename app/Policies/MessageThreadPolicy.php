<?php

namespace App\Policies;

use App\Models\MessageThread;
use App\Models\User;

class MessageThreadPolicy
{
    public function view(User $user, MessageThread $thread): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $thread->user_id === $user->id
            || strcasecmp((string) $thread->contact_email, (string) $user->email) === 0;
    }

    public function reply(User $user, MessageThread $thread): bool
    {
        return $this->view($user, $thread) && ! $thread->isClosed();
    }
}
