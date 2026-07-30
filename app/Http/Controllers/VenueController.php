<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(Request $request): View
    {
        $venues = Venue::query()
            ->approved()
            ->with(['waters.species', 'manager', 'photos'])
            ->when($request->filled('species'), function ($query) use ($request) {
                $query->whereHas('waters.species', fn ($q) => $q->where('species.slug', $request->string('species')));
            })
            ->when($request->filled('ticket_type'), function ($query) use ($request) {
                $query->where('ticket_type', $request->string('ticket_type'));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', $q)
                        ->orWhere('address', 'like', $q)
                        ->orWhere('overview', 'like', $q);
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('venues.index', [
            'venues' => $venues,
            'species' => Species::orderBy('name')->get(),
            'filters' => $request->only(['q', 'species', 'ticket_type']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Venue::class);

        return view('venues.create');
    }

    public function show(Venue $venue): View
    {
        $this->authorize('view', $venue);

        $venue->load([
            'waters.species',
            'waters.pegs' => fn ($q) => $q->with('photos')->orderBy('sort_order')->orderBy('id'),
            'manager',
            'photos',
            'matchReports' => fn ($q) => $q->whereNotNull('published_at')->latest('published_at')->limit(10),
            'announcements' => fn ($q) => $q->whereNotNull('published_at')->latest('published_at')->limit(10),
            'fishingSessions' => fn ($q) => $q->with(['user', 'water', 'waterPeg', 'catches.species', 'photos'])->latest('fished_at')->limit(8),
            'anglerTactics' => fn ($q) => $q->with(['user', 'water'])->limit(20),
        ]);

        return view('venues.show', [
            'venue' => $venue,
            'speciesList' => $venue->allSpecies(),
            'anglerTactics' => $venue->anglerTactics,
            'pendingPegs' => $venue->waters->flatMap->pegs->where('is_verified', false)->values(),
        ]);
    }

    public function edit(Venue $venue): View
    {
        $this->authorize('manage', $venue);

        $venue->load('waters.species');

        return view('venues.edit', [
            'venue' => $venue,
        ]);
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        $this->authorize('delete', $venue);
        $venue->delete();

        return redirect()->route('venues.index')->with('status', 'Venue deleted.');
    }
}
