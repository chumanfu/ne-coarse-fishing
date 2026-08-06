<?php

namespace App\Listeners;

use App\Services\MessagingService;
use Illuminate\Auth\Events\Registered;

class SendWelcomeMessage
{
    public function __construct(
        private MessagingService $messaging,
    ) {}

    public function handle(Registered $event): void
    {
        $this->messaging->sendWelcome($event->user);
    }
}
