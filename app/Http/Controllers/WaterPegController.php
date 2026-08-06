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

        return view('pegs.form', [
            'venue' => $venue,
            'peg' => null,
        ]);
    }

    public function store(Request $request, Venue $venue, WaterPegService $pegs): RedirectResponse
    {
        abort_unless($venue->canManagePegs($request->user()), 403);

        $validated = $this->validatedPeg($request);
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

    public function edit(Venue $venue, WaterPeg $waterPeg): View
    {
        abort_unless($venue->canManagePegs(request()->user()), 403);
        abort_unless($waterPeg->water?->venue_id === $venue->id, 404);
        $venue->load('waters');

        return view('pegs.form', [
            'venue' => $venue,
            'peg' => $waterPeg->load('photos'),
        ]);
    }

    public function update(Request $request, Venue $venue, WaterPeg $waterPeg, WaterPegService $pegs): RedirectResponse
    {
        abort_unless($venue->canManagePegs($request->user()), 403);
        abort_unless($waterPeg->water?->venue_id === $venue->id, 404);

        $validated = $this->validatedPeg($request);
        $water = $venue->waters()->whereKey($validated['water_id'])->firstOrFail();

        $pegs->updateForWater(
            $waterPeg,
            $water,
            $request->user(),
            [
                'name' => $validated['name'] ?? null,
                'number' => $validated['number'] ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            $request->file('photos', []),
            collect($request->input('keep_photo_ids', []))->map(fn ($id) => (int) $id)->all(),
        );

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Peg updated.');
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

    /**
     * @return array{water_id: int, number: ?string, name: ?string, latitude: float, longitude: float}
     */
    private function validatedPeg(Request $request): array
    {
        $validated = $request->validate([
            'water_id' => ['required', 'exists:waters,id'],
            'number' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['image', 'max:5120'],
            'keep_photo_ids' => ['nullable', 'array'],
            'keep_photo_ids.*' => ['integer'],
        ]);

        abort_unless(
            filled($validated['number'] ?? null) || filled($validated['name'] ?? null),
            422,
            'Give the peg a number and/or name.'
        );

        return $validated;
    }
}
