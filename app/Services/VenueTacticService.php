<?php

namespace App\Services;

use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueTactic;

class VenueTacticService
{
    public function syncFromSession(FishingSession $session, ?string $body): void
    {
        $body = filled($body) ? trim($body) : null;
        $existing = $session->venueTactic;

        if ($body === null) {
            $existing?->delete();

            return;
        }

        $tactic = VenueTactic::query()->updateOrCreate(
            ['fishing_session_id' => $session->id],
            [
                'venue_id' => $session->venue_id,
                'user_id' => $session->user_id,
                'water_id' => $session->water_id,
                'peg_number' => $session->peg_number,
                'body' => $body,
                'fished_at' => $session->fished_at,
            ]
        );

        if ($tactic->wasRecentlyCreated) {
            app(ActivityLogger::class)->tacticShared($tactic);
        }
    }

    public function createStandalone(User $user, Venue $venue, array $data): VenueTactic
    {
        if (! empty($data['water_id'])) {
            abort_unless($venue->waters()->whereKey($data['water_id'])->exists(), 422);
        }

        $tactic = VenueTactic::query()->create([
            'venue_id' => $venue->id,
            'user_id' => $user->id,
            'water_id' => $data['water_id'] ?? null,
            'peg_number' => $data['peg_number'] ?? null,
            'body' => trim($data['body']),
            'fished_at' => $data['fished_at'] ?? null,
        ]);

        app(ActivityLogger::class)->tacticShared($tactic);

        return $tactic;
    }
}
