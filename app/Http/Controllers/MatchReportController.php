<?php

namespace App\Http\Controllers;

use App\Models\MatchReport;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchReportController extends Controller
{
    public function create(Venue $venue): View
    {
        $this->authorize('manage', $venue);
        $venue->load('waters');

        return view('match-reports.create', compact('venue'));
    }

    public function store(Request $request, Venue $venue): RedirectResponse
    {
        $this->authorize('manage', $venue);

        $validated = $request->validate([
            'water_id' => ['nullable', 'exists:waters,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
        ]);

        if (! empty($validated['water_id'])) {
            abort_unless($venue->waters()->whereKey($validated['water_id'])->exists(), 422);
        }

        $venue->matchReports()->create([
            'user_id' => $request->user()->id,
            'water_id' => $validated['water_id'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Match report published.')
            ->withFragment('official');
    }

    public function destroy(MatchReport $matchReport): RedirectResponse
    {
        $this->authorize('manage', $matchReport->venue);
        $venue = $matchReport->venue;
        $matchReport->delete();

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Match report removed.')
            ->withFragment('official');
    }
}
