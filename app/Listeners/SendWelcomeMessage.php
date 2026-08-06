<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use App\Services\MessagingService;
use Illuminate\Auth\Events\Registered;

class SendWelcomeMessage
{
    public function __construct(
        private MessagingService $messaging,
        private ActivityLogger $activities,
    ) {}

    public function handle(Registered $event): void
    {
        $this->activities->userRegistered($event->user);
        $this->messaging->sendWelcome($event->user);
    }
}
