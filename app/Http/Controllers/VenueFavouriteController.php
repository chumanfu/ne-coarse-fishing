<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueFavouriteController extends Controller
{
    public function index(Request $request): View
    {
        $venues = $request->user()
            ->favouriteVenues()
            ->approved()
            ->with(['waters.species', 'manager', 'photos', 'clubs' => fn ($q) => $q->published()->ordered()])
            ->orderBy('name')
            ->paginate(12);

        return view('venues.favourites', [
            'venues' => $venues,
        ]);
    }

    public function store(Request $request, Venue $venue): RedirectResponse
    {
        $this->authorize('view', $venue);

        $request->user()->favouriteVenues()->syncWithoutDetaching([$venue->id]);

        return back()->with('status', "{$venue->name} added to your favourites.");
    }

    public function destroy(Request $request, Venue $venue): RedirectResponse
    {
        $request->user()->favouriteVenues()->detach($venue->id);

        return back()->with('status', "{$venue->name} removed from your favourites.");
    }
}
