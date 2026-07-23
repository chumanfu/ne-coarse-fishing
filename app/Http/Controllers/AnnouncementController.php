<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function create(Venue $venue): View
    {
        $this->authorize('manage', $venue);

        return view('announcements.create', compact('venue'));
    }

    public function store(Request $request, Venue $venue): RedirectResponse
    {
        $this->authorize('manage', $venue);

        $validated = $request->validate([
            'type' => ['required', 'in:announcement,stocking'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
        ]);

        $venue->announcements()->create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Announcement published.')
            ->withFragment('official');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('manage', $announcement->venue);
        $venue = $announcement->venue;
        $announcement->delete();

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Announcement removed.')
            ->withFragment('official');
    }
}
