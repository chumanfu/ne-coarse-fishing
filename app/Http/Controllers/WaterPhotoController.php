<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPhoto;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WaterPhotoController extends Controller
{
    public function store(Request $request, Venue $venue, Water $water): RedirectResponse
    {
        abort_unless($water->venue_id === $venue->id, 404);
        abort_unless($request->user() !== null, 403);

        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $path = Uploads::store($validated['photo'], 'water-photos');

        $water->photos()->create([
            'user_id' => $request->user()->id,
            'image_path' => $path,
            'is_approved' => false,
            'sort_order' => (int) $water->photos()->max('sort_order') + 1,
        ]);

        return back()->with('status', 'Photo uploaded. It will appear once a venue manager approves it.');
    }

    public function approve(Venue $venue, Water $water, WaterPhoto $waterPhoto): RedirectResponse
    {
        abort_unless($water->venue_id === $venue->id, 404);
        abort_unless($waterPhoto->water_id === $water->id, 404);
        abort_unless($venue->canManagePegs(request()->user()), 403);

        $waterPhoto->markApproved(request()->user());

        return back()->with('status', 'Water photo approved.');
    }

    public function destroy(Venue $venue, Water $water, WaterPhoto $waterPhoto): RedirectResponse
    {
        abort_unless($water->venue_id === $venue->id, 404);
        abort_unless($waterPhoto->water_id === $water->id, 404);

        $user = request()->user();
        $canManage = $venue->canManagePegs($user);
        $isOwner = $user && $waterPhoto->user_id === $user->id && ! $waterPhoto->is_approved;

        abort_unless($canManage || $isOwner, 403);

        $waterPhoto->delete();

        return back()->with('status', 'Water photo removed.');
    }
}
