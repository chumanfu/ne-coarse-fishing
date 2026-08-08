<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WaterVideoController extends Controller
{
    public function store(Request $request, Venue $venue, Water $water): RedirectResponse
    {
        abort_unless($water->venue_id === $venue->id, 404);
        abort_unless($request->user() !== null, 403);

        $validated = $request->validate([
            'youtube_url' => ['required', 'string', 'max:500'],
            'title' => ['nullable', 'string', 'max:120'],
        ]);

        $youtubeId = WaterVideo::extractYoutubeId($validated['youtube_url']);

        if ($youtubeId === null) {
            throw ValidationException::withMessages([
                'youtube_url' => 'Enter a valid YouTube video URL.',
            ]);
        }

        $water->videos()->create([
            'user_id' => $request->user()->id,
            'youtube_url' => $validated['youtube_url'],
            'youtube_id' => $youtubeId,
            'title' => $validated['title'] ?: null,
            'is_approved' => false,
            'sort_order' => (int) $water->videos()->max('sort_order') + 1,
        ]);

        return back()->with('status', 'Video submitted. It will appear once a venue manager approves it.');
    }

    public function approve(Venue $venue, Water $water, WaterVideo $waterVideo): RedirectResponse
    {
        abort_unless($water->venue_id === $venue->id, 404);
        abort_unless($waterVideo->water_id === $water->id, 404);
        abort_unless($venue->canManagePegs(request()->user()), 403);

        $waterVideo->markApproved(request()->user());

        return back()->with('status', 'Water video approved.');
    }

    public function destroy(Venue $venue, Water $water, WaterVideo $waterVideo): RedirectResponse
    {
        abort_unless($water->venue_id === $venue->id, 404);
        abort_unless($waterVideo->water_id === $water->id, 404);

        $user = request()->user();
        $canManage = $venue->canManagePegs($user);
        $isOwner = $user && $waterVideo->user_id === $user->id && ! $waterVideo->is_approved;

        abort_unless($canManage || $isOwner, 403);

        $waterVideo->delete();

        return back()->with('status', 'Water video removed.');
    }
}
