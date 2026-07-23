<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\VenueClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VenueClaimController extends Controller
{
    public function store(Request $request, Venue $venue): RedirectResponse
    {
        $this->authorize('claim', $venue);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        VenueClaim::create([
            'venue_id' => $venue->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Ownership claim submitted. An admin will review it shortly.');
    }
}
