<?php

namespace App\Services;

use App\Filament\Resources\ClubClaims\ClubClaimResource;
use App\Filament\Resources\ClubEditRequests\ClubEditRequestResource;
use App\Filament\Resources\MessageThreads\MessageThreadResource;
use App\Filament\Resources\TackleShopClaims\TackleShopClaimResource;
use App\Filament\Resources\TackleShopEditRequests\TackleShopEditRequestResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\VenueClaims\VenueClaimResource;
use App\Filament\Resources\VenueEditRequests\VenueEditRequestResource;
use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Club;
use App\Models\ClubClaim;
use App\Models\ClubEditRequest;
use App\Models\FishingSession;
use App\Models\MatchReport;
use App\Models\MessageThread;
use App\Models\TackleReview;
use App\Models\TackleShop;
use App\Models\TackleShopClaim;
use App\Models\TackleShopEditRequest;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueClaim;
use App\Models\VenueEditRequest;
use App\Models\VenueTactic;
use App\Models\WaterPeg;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function userRegistered(User $user): void
    {
        $this->log(
            Activity::TYPE_USER_REGISTERED,
            $user,
            $user,
            $user->name.' joined NE Coarse Fishing',
            $user->email,
            UserResource::getUrl('edit', ['record' => $user]),
        );
    }

    public function venueSubmitted(Venue $venue, ?User $user = null): void
    {
        $this->log(
            Activity::TYPE_VENUE_SUBMITTED,
            $venue,
            $user ?? $venue->creator,
            'Venue submitted: '.$venue->name,
            'Awaiting approval',
            route('venues.show', $venue, absolute: false),
        );
    }

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

    public function venueClaimed(VenueClaim $claim): void
    {
        $claim->loadMissing('venue', 'user');

        $this->log(
            Activity::TYPE_VENUE_CLAIM,
            $claim,
            $claim->user,
            ($claim->user?->name ?? 'An angler').' claimed '.$claim->venue->name,
            $claim->message ? str($claim->message)->limit(80)->toString() : 'Pending review',
            VenueClaimResource::getUrl('index'),
        );
    }

    public function venueEditSuggested(VenueEditRequest $request): void
    {
        $request->loadMissing('venue', 'user');

        $this->log(
            Activity::TYPE_VENUE_EDIT_REQUEST,
            $request,
            $request->user,
            ($request->user?->name ?? 'An angler').' suggested edits to '.$request->venue->name,
            $request->message ? str($request->message)->limit(80)->toString() : 'Pending review',
            VenueEditRequestResource::getUrl('view', ['record' => $request]),
        );
    }

    public function clubClaimed(ClubClaim $claim): void
    {
        $claim->loadMissing('club', 'user');

        $this->log(
            Activity::TYPE_CLUB_CLAIM,
            $claim,
            $claim->user,
            ($claim->user?->name ?? 'An angler').' claimed club '.$claim->club->name,
            $claim->message ? str($claim->message)->limit(80)->toString() : 'Pending review',
            ClubClaimResource::getUrl('index'),
        );
    }

    public function clubEditSuggested(ClubEditRequest $request): void
    {
        $request->loadMissing('club', 'user');

        $this->log(
            Activity::TYPE_CLUB_EDIT_REQUEST,
            $request,
            $request->user,
            ($request->user?->name ?? 'An angler').' suggested edits to club '.$request->club->name,
            $request->message ? str($request->message)->limit(80)->toString() : 'Pending review',
            ClubEditRequestResource::getUrl('view', ['record' => $request]),
        );
    }

    public function tackleShopClaimed(TackleShopClaim $claim): void
    {
        $claim->loadMissing('tackleShop', 'user');

        $this->log(
            Activity::TYPE_SHOP_CLAIM,
            $claim,
            $claim->user,
            ($claim->user?->name ?? 'An angler').' claimed shop '.$claim->tackleShop->name,
            $claim->message ? str($claim->message)->limit(80)->toString() : 'Pending review',
            TackleShopClaimResource::getUrl('index'),
        );
    }

    public function tackleShopEditSuggested(TackleShopEditRequest $request): void
    {
        $request->loadMissing('tackleShop', 'user');

        $this->log(
            Activity::TYPE_SHOP_EDIT_REQUEST,
            $request,
            $request->user,
            ($request->user?->name ?? 'An angler').' suggested edits to shop '.$request->tackleShop->name,
            $request->message ? str($request->message)->limit(80)->toString() : 'Pending review',
            TackleShopEditRequestResource::getUrl('view', ['record' => $request]),
        );
    }

    public function pegAdded(WaterPeg $peg, User $user, Venue $venue): void
    {
        $peg->loadMissing('water');

        $this->log(
            Activity::TYPE_PEG,
            $peg,
            $user,
            $user->name.' added peg '.$peg->label().' at '.$venue->name,
            $peg->water?->name,
            route('venues.show', $venue, absolute: false),
        );
    }

    public function matchReportPublished(MatchReport $report): void
    {
        $report->loadMissing('venue', 'user');

        $this->log(
            Activity::TYPE_MATCH_REPORT,
            $report,
            $report->user,
            ($report->user?->name ?? 'An angler').' published a match report at '.$report->venue->name,
            $report->title,
            route('venues.show', $report->venue, absolute: false).'#official',
        );
    }

    public function announcementPublished(Announcement $announcement): void
    {
        $announcement->loadMissing('venue', 'user');

        $this->log(
            Activity::TYPE_ANNOUNCEMENT,
            $announcement,
            $announcement->user,
            ($announcement->user?->name ?? 'An angler').' posted an announcement at '.$announcement->venue->name,
            $announcement->title,
            route('venues.show', $announcement->venue, absolute: false).'#official',
        );
    }

    public function tackleReviewPublished(TackleReview $review): void
    {
        $review->loadMissing('user');

        $this->log(
            Activity::TYPE_TACKLE_REVIEW,
            $review,
            $review->user,
            ($review->user?->name ?? 'An angler').' reviewed '.$review->title,
            $review->brand ?: ($review->rating.'★'),
            route('tackle-reviews.show', $review, absolute: false),
        );
    }

    public function messageReceived(MessageThread $thread, ?User $user = null): void
    {
        $this->log(
            Activity::TYPE_MESSAGE,
            $thread,
            $user ?? $thread->user,
            'New message: '.$thread->subject,
            $thread->contact_name.' <'.$thread->contact_email.'>',
            MessageThreadResource::getUrl('view', ['record' => $thread]),
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
