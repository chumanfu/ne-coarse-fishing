<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\WaterPeg;
use App\Services\WaterPegService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaterPegController extends Controller
{
    public function create(Venue $venue): View
    {
        abort_unless($venue->canManagePegs(request()->user()), 403);
        $venue->load('waters');

        return view('pegs.create', [
            'venue' => $venue,
        ]);
    }

    public function store(Request $request, Venue $venue, WaterPegService $pegs): RedirectResponse
    {
        abort_unless($venue->canManagePegs($request->user()), 403);

        $validated = $request->validate([
            'water_id' => ['required', 'exists:waters,id'],
            'number' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        abort_unless(
            filled($validated['number'] ?? null) || filled($validated['name'] ?? null),
            422,
            'Give the peg a number and/or name.'
        );

        $water = $venue->waters()->whereKey($validated['water_id'])->firstOrFail();

        $pegs->createForWater(
            $water,
            $request->user(),
            [
                'name' => $validated['name'] ?? null,
                'number' => $validated['number'] ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            true,
            $request->file('photos', []),
        );

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Peg added to the official list.');
    }

    public function verify(Venue $venue, WaterPeg $waterPeg): RedirectResponse
    {
        abort_unless($venue->canManagePegs(request()->user()), 403);
        abort_unless($waterPeg->water?->venue_id === $venue->id, 404);

        $waterPeg->markVerified(request()->user());

        return back()->with('status', 'Peg verified and added to the official list with any photos.');
    }

    public function destroy(Venue $venue, WaterPeg $waterPeg): RedirectResponse
    {
        abort_unless($venue->canManagePegs(request()->user()), 403);
        abort_unless($waterPeg->water?->venue_id === $venue->id, 404);

        $waterPeg->fishingSessions()->update(['water_peg_id' => null]);
        $waterPeg->delete();

        return back()->with('status', 'Peg removed.');
    }
}
