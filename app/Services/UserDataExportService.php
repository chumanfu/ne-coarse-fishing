<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\MatchReport;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\SiteAnnouncement;
use App\Models\TackleReview;
use App\Models\User;
use App\Models\Venue;
use App\Models\WaterPeg;
use App\Support\Uploads;
use Illuminate\Support\Carbon;

class UserDataExportService
{
    /**
     * @return array<string, mixed>
     */
    public function export(User $user): array
    {
        $user->load([
            'clubs:id,name,slug,town',
            'favouriteVenues:id,name,slug',
            'roles:id,name',
        ]);

        $threads = MessageThread::query()
            ->forParticipant($user)
            ->with(['messages' => fn ($q) => $q->orderBy('created_at')->orderBy('id')])
            ->orderBy('last_message_at')
            ->get();

        $sessions = $user->fishingSessions()
            ->with(['venue:id,name,slug', 'water:id,name', 'photos', 'catches.species:id,name', 'venueTactic'])
            ->orderByDesc('fished_at')
            ->get();

        $submittedVenues = $user->venues()
            ->with(['photos', 'waters:id,venue_id,name'])
            ->orderBy('name')
            ->get();

        $managedVenues = $user->managedVenues()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'manager_id', 'manager_verified']);

        $tackleReviews = TackleReview::query()
            ->where('user_id', $user->id)
            ->with('photos')
            ->latest()
            ->get();

        $pegsCreated = WaterPeg::query()
            ->where('created_by', $user->id)
            ->with('photos')
            ->get();

        return [
            'exported_at' => now()->toIso8601String(),
            'export_version' => 1,
            'account' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => optional($user->email_verified_at)?->toIso8601String(),
                'roles' => $user->roles->pluck('name')->values()->all(),
                'created_at' => optional($user->created_at)?->toIso8601String(),
                'updated_at' => optional($user->updated_at)?->toIso8601String(),
            ],
            'clubs' => $user->clubs->map(fn ($club) => [
                'id' => $club->id,
                'name' => $club->name,
                'slug' => $club->slug,
                'town' => $club->town,
            ])->values()->all(),
            'favourite_venues' => $user->favouriteVenues->map(fn ($venue) => [
                'id' => $venue->id,
                'name' => $venue->name,
                'slug' => $venue->slug,
            ])->values()->all(),
            'message_threads' => $threads->map(function (MessageThread $thread) {
                return [
                    'id' => $thread->id,
                    'subject' => $thread->subject,
                    'contact_name' => $thread->contact_name,
                    'contact_email' => $thread->contact_email,
                    'source' => $thread->source,
                    'status' => $thread->status,
                    'last_message_at' => optional($thread->last_message_at)?->toIso8601String(),
                    'messages' => $thread->messages->map(fn (Message $message) => [
                        'id' => $message->id,
                        'body' => $message->body,
                        'is_from_admin' => $message->is_from_admin,
                        'user_id' => $message->user_id,
                        'created_at' => optional($message->created_at)?->toIso8601String(),
                    ])->values()->all(),
                ];
            })->values()->all(),
            'fishing_sessions' => $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'venue' => $session->venue?->only(['id', 'name', 'slug']),
                    'water' => $session->water?->only(['id', 'name']),
                    'fished_at' => optional($session->fished_at)?->toDateString(),
                    'duration_hours' => $session->duration_hours,
                    'weather' => $session->weather,
                    'peg_number' => $session->peg_number,
                    'peg_latitude' => $session->peg_latitude,
                    'peg_longitude' => $session->peg_longitude,
                    'commentary' => $session->commentary,
                    'tactics_tip' => $session->tactics_tip,
                    'photos' => $session->photos->map(fn ($photo) => [
                        'id' => $photo->id,
                        'url' => method_exists($photo, 'url') ? $photo->url() : Uploads::url($photo->image_path ?? $photo->path ?? ''),
                    ])->values()->all(),
                    'catches' => $session->catches->map(fn ($catch) => [
                        'id' => $catch->id,
                        'species' => $catch->species?->name,
                        'weight_lb' => $catch->weight_lb,
                        'bait' => $catch->bait,
                        'quantity' => $catch->quantity,
                    ])->values()->all(),
                    'created_at' => optional($session->created_at)?->toIso8601String(),
                ];
            })->values()->all(),
            'submitted_venues' => $submittedVenues->map(function (Venue $venue) {
                return [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'slug' => $venue->slug,
                    'address' => $venue->address,
                    'is_approved' => $venue->is_approved,
                    'photos' => $venue->photos->map(fn ($photo) => [
                        'id' => $photo->id,
                        'url' => method_exists($photo, 'url') ? $photo->url() : null,
                    ])->values()->all(),
                    'waters' => $venue->waters->map(fn ($water) => $water->only(['id', 'name']))->values()->all(),
                ];
            })->values()->all(),
            'managed_venues' => $managedVenues->map(fn (Venue $venue) => [
                'id' => $venue->id,
                'name' => $venue->name,
                'slug' => $venue->slug,
                'manager_verified' => $venue->manager_verified,
            ])->values()->all(),
            'venue_claims' => $user->venueClaims()->with('venue:id,name,slug')->latest()->get()->map(fn ($claim) => [
                'id' => $claim->id,
                'venue' => $claim->venue?->only(['id', 'name', 'slug']),
                'status' => $claim->status,
                'created_at' => optional($claim->created_at)?->toIso8601String(),
            ])->values()->all(),
            'venue_edit_requests' => $user->venueEditRequests()->with('venue:id,name,slug')->latest()->get()->map(fn ($request) => [
                'id' => $request->id,
                'venue' => $request->venue?->only(['id', 'name', 'slug']),
                'status' => $request->status,
                'message' => $request->message,
                'proposed_data' => $request->proposed_data,
                'created_at' => optional($request->created_at)?->toIso8601String(),
            ])->values()->all(),
            'venue_tactics' => $user->venueTactics()->with('venue:id,name,slug')->latest()->get()->map(fn ($tactic) => [
                'id' => $tactic->id,
                'venue' => $tactic->venue?->only(['id', 'name', 'slug']),
                'body' => $tactic->body,
                'peg_number' => $tactic->peg_number,
                'fished_at' => optional($tactic->fished_at)?->toDateString(),
                'created_at' => optional($tactic->created_at)?->toIso8601String(),
            ])->values()->all(),
            'tackle_reviews' => $tackleReviews->map(fn (TackleReview $review) => [
                'id' => $review->id,
                'title' => $review->title,
                'brand' => $review->brand,
                'rating' => $review->rating,
                'body' => $review->body,
                'purchase_url' => $review->purchase_url,
                'is_published' => $review->is_published,
                'featured_on_home' => $review->featured_on_home,
                'photos' => $review->photos->map(fn ($photo) => [
                    'id' => $photo->id,
                    'url' => method_exists($photo, 'url') ? $photo->url() : null,
                ])->values()->all(),
                'created_at' => optional($review->created_at)?->toIso8601String(),
            ])->values()->all(),
            'activities' => Activity::query()
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(fn (Activity $activity) => [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'title' => $activity->title,
                    'summary' => $activity->summary,
                    'url' => $activity->url,
                    'created_at' => optional($activity->created_at)?->toIso8601String(),
                ])->values()->all(),
            'match_reports' => MatchReport::query()
                ->where('user_id', $user->id)
                ->with('venue:id,name,slug')
                ->latest('published_at')
                ->get()
                ->map(fn (MatchReport $report) => [
                    'id' => $report->id,
                    'venue' => $report->venue?->only(['id', 'name', 'slug']),
                    'title' => $report->title,
                    'body' => $report->body,
                    'published_at' => optional($report->published_at)?->toIso8601String(),
                ])->values()->all(),
            'announcements' => Announcement::query()
                ->where('user_id', $user->id)
                ->with('venue:id,name,slug')
                ->latest('published_at')
                ->get()
                ->map(fn (Announcement $announcement) => [
                    'id' => $announcement->id,
                    'venue' => $announcement->venue?->only(['id', 'name', 'slug']),
                    'type' => $announcement->type,
                    'title' => $announcement->title,
                    'body' => $announcement->body,
                    'published_at' => optional($announcement->published_at)?->toIso8601String(),
                ])->values()->all(),
            'site_announcements' => SiteAnnouncement::query()
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(fn (SiteAnnouncement $announcement) => [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'body' => $announcement->body,
                    'level' => $announcement->level,
                    'starts_at' => optional($announcement->starts_at)?->toIso8601String(),
                    'ends_at' => optional($announcement->ends_at)?->toIso8601String(),
                    'is_active' => $announcement->is_active,
                ])->values()->all(),
            'water_pegs_created' => $pegsCreated->map(fn (WaterPeg $peg) => [
                'id' => $peg->id,
                'label' => method_exists($peg, 'label') ? $peg->label() : ($peg->peg_number ?? null),
                'latitude' => $peg->latitude,
                'longitude' => $peg->longitude,
                'is_verified' => $peg->is_verified,
                'photos' => $peg->photos->map(fn ($photo) => [
                    'id' => $photo->id,
                    'url' => method_exists($photo, 'url') ? $photo->url() : null,
                ])->values()->all(),
            ])->values()->all(),
            'notes' => [
                'Passwords and remember tokens are never included in this export.',
                'Photo entries include URLs where available; download those files separately if you want local copies.',
            ],
        ];
    }

    public function filename(User $user): string
    {
        $stamp = Carbon::now()->format('Y-m-d_His');

        return 'ne-coarse-fishing-data-export-'.$user->id.'-'.$stamp.'.json';
    }
}
