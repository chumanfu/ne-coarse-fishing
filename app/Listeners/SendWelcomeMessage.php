<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\MessagingService;
use App\Services\SuperAdminNotifier;
use Illuminate\Auth\Events\Registered;

class SendWelcomeMessage
{
    public function __construct(
        private MessagingService $messaging,
        private ActivityLogger $activities,
        private SuperAdminNotifier $admins,
    ) {}

    public function handle(Registered $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->activities->userRegistered($event->user);
        $this->messaging->sendWelcome($event->user);
        $this->admins->userSignedUp($event->user);
    }
}
