<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\Venue;
use App\Models\VenueTactic;
use App\Services\VenueTacticService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueTacticController extends Controller
{
    public function create(Venue $venue): View
    {
        $this->authorize('view', $venue);
        abort_unless($venue->is_approved, 404);

        $venue->load('waters');

        return view('tactics.create', [
            'venue' => $venue,
            'species' => Species::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Venue $venue, VenueTacticService $tactics): RedirectResponse
    {
        $this->authorize('view', $venue);
        abort_unless($venue->is_approved, 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'water_id' => ['nullable', 'exists:waters,id'],
            'peg_number' => ['nullable', 'string', 'max:50'],
            'fished_at' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $tactic = $tactics->createStandalone($request->user(), $venue, $validated);

        app(\App\Services\ActivityLogger::class)->tacticShared($tactic);

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Tactics tip added. Thanks for sharing!');
    }

    public function edit(VenueTactic $venueTactic): View
    {
        $this->authorize('update', $venueTactic);

        $venueTactic->load(['venue.waters']);

        return view('tactics.edit', [
            'tactic' => $venueTactic,
            'venue' => $venueTactic->venue,
        ]);
    }

    public function update(Request $request, VenueTactic $venueTactic): RedirectResponse
    {
        $this->authorize('update', $venueTactic);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'water_id' => ['nullable', 'exists:waters,id'],
            'peg_number' => ['nullable', 'string', 'max:50'],
            'fished_at' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        if (! empty($validated['water_id'])) {
            abort_unless($venueTactic->venue->waters()->whereKey($validated['water_id'])->exists(), 422);
        }

        $venueTactic->update([
            'body' => trim($validated['body']),
            'water_id' => $validated['water_id'] ?? null,
            'peg_number' => $validated['peg_number'] ?? null,
            'fished_at' => $validated['fished_at'] ?? null,
        ]);

        if ($venueTactic->fishingSession) {
            $venueTactic->fishingSession->update([
                'tactics_tip' => trim($validated['body']),
                'water_id' => $validated['water_id'] ?? $venueTactic->fishingSession->water_id,
                'peg_number' => $validated['peg_number'] ?? $venueTactic->fishingSession->peg_number,
            ]);
        }

        return redirect()
            ->route('venues.show', $venueTactic->venue)
            ->with('status', 'Tactics tip updated.');
    }

    public function destroy(VenueTactic $venueTactic): RedirectResponse
    {
        $this->authorize('delete', $venueTactic);

        $venue = $venueTactic->venue;

        if ($venueTactic->fishingSession) {
            $venueTactic->fishingSession->update(['tactics_tip' => null]);
        }

        $venueTactic->delete();

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Tactics tip removed.');
    }
}
