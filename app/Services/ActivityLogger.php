<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Club;
use App\Models\FishingSession;
use App\Models\TackleShop;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueTactic;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function venueAdded(Venue $venue, ?User $user = null): void
    {
        if (! $venue->is_approved) {
            return;
        }

        $this->log(
            Activity::TYPE_VENUE,
            $venue,
            $user ?? $venue->creator,
            'New venue: '.$venue->name,
            $venue->address ?: $venue->ticketTypeLabel(),
            route('venues.show', $venue, absolute: false),
        );
    }

    public function sessionLogged(FishingSession $session): void
    {
        $session->loadMissing('venue', 'user');

        $this->log(
            Activity::TYPE_SESSION,
            $session,
            $session->user,
            ($session->user?->name ?? 'An angler').' logged a session at '.$session->venue->name,
            $session->peg_number ? 'Peg '.$session->peg_number : $session->fished_at?->format('d M Y'),
            route('sessions.show', $session, absolute: false),
        );
    }

    public function tacticShared(VenueTactic $tactic): void
    {
        $tactic->loadMissing('venue', 'user');

        $this->log(
            Activity::TYPE_TACTIC,
            $tactic,
            $tactic->user,
            ($tactic->user?->name ?? 'An angler').' shared a tactic at '.$tactic->venue->name,
            str($tactic->body)->limit(80)->toString(),
            route('venues.show', $tactic->venue, absolute: false).'#tactics',
        );
    }

    public function clubAdded(Club $club): void
    {
        if (! $club->is_published) {
            return;
        }

        $this->log(
            Activity::TYPE_CLUB,
            $club,
            null,
            'Club added: '.$club->name,
            $club->town,
            route('clubs.show', $club, absolute: false),
        );
    }

    public function tackleShopAdded(TackleShop $shop): void
    {
        if (! $shop->is_published) {
            return;
        }

        $this->log(
            Activity::TYPE_TACKLE_SHOP,
            $shop,
            null,
            'Tackle shop added: '.$shop->name,
            $shop->town ?: $shop->locationTypeLabel(),
            route('tackle-shops.show', $shop, absolute: false),
        );
    }

    private function log(string $type, Model $subject, ?User $user, string $title, ?string $summary, string $url): void
    {
        Activity::query()->create([
            'type' => $type,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'user_id' => $user?->id,
            'title' => $title,
            'summary' => $summary,
            'url' => $url,
        ]);
    }
}
