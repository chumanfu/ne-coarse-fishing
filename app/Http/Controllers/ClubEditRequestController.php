<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubEditRequest;
use App\Services\ClubPersistenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubEditRequestController extends Controller
{
    public function create(Club $club): View
    {
        $this->authorize('suggestEdit', $club);

        return view('clubs.suggest-edit', [
            'club' => $club,
        ]);
    }

    public function store(Request $request, Club $club, ClubPersistenceService $persistence): RedirectResponse
    {
        $this->authorize('suggestEdit', $club);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'overview' => ['nullable', 'string', 'max:5000'],
            'town' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        ClubEditRequest::query()->create([
            'club_id' => $club->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'] ?? null,
            'proposed_data' => $persistence->proposedFromInput($validated),
            'status' => 'pending',
        ]);

        return redirect()
            ->route('clubs.show', $club)
            ->with('status', 'Suggested club edit submitted for admin review.');
    }
}
