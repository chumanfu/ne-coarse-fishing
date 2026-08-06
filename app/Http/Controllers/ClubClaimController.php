<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClubClaimController extends Controller
{
    public function store(Request $request, Club $club): RedirectResponse
    {
        $this->authorize('claim', $club);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        ClubClaim::query()->create([
            'club_id' => $club->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('clubs.show', $club)
            ->with('status', 'Club ownership claim submitted. An admin will review it shortly.');
    }
}
