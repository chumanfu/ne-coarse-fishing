<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\Venue;
use App\Services\PegCatchStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(Request $request): View
    {
        $venues = Venue::query()
            ->approved()
            ->with(['waters.species', 'manager', 'photos', 'clubs' => fn ($q) => $q->published()->ordered()])
            ->when($request->user(), function ($query) use ($request) {
                $query->withExists([
                    'favouritedBy as is_favourited' => fn ($q) => $q->where('favourite_venues.user_id', $request->user()->id),
                ]);
            })
            ->when($request->filled('species'), function ($query) use ($request) {
                $query->whereHas('waters.species', fn ($q) => $q->where('species.slug', $request->string('species')));
            })
            ->when($request->filled('ticket_type'), function ($query) use ($request) {
                $query->where('ticket_type', $request->string('ticket_type'));
            })
            ->when($request->filled('club_link'), function ($query) use ($request) {
                match ((string) $request->string('club_link')) {
                    'club' => $query->whereHas('clubs'),
                    'independent' => $query->whereDoesntHave('clubs'),
                    default => null,
                };
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
            'filters' => $request->only(['q', 'species', 'ticket_type', 'club_link']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Venue::class);

        return view('venues.create');
    }

    public function show(Venue $venue, PegCatchStatsService $pegStats): View
    {
        $this->authorize('view', $venue);

        $venue->load([
            'waters.species',
            'waters.pegs' => fn ($q) => $q->with('photos')->orderBy('sort_order')->orderBy('id'),
            'waters.photos' => fn ($q) => $q->with('uploader')->orderBy('sort_order')->orderBy('id'),
            'waters.videos' => fn ($q) => $q->with('uploader')->orderBy('sort_order')->orderBy('id'),
            'manager',
            'photos',
            'matchReports' => fn ($q) => $q->whereNotNull('published_at')->latest('published_at')->limit(10),
            'announcements' => fn ($q) => $q->currentlyVisible()->latest('published_at')->limit(10),
            'fishingSessions' => fn ($q) => $q->with(['user', 'water', 'waterPeg', 'catches.species', 'photos'])->latest('fished_at')->limit(8),
            'anglerTactics' => fn ($q) => $q->with(['user', 'water'])->limit(20),
            'clubs' => fn ($q) => $q->published()->ordered(),
        ]);

        $mappedPegs = $venue->waters
            ->flatMap(fn ($water) => $water->pegs
                ->where('is_verified', true)
                ->filter(fn ($peg) => $peg->hasMapPosition()))
            ->values();

        $pegMapPayloads = collect($pegStats->mapPayloads($mappedPegs))
            ->groupBy(fn (array $payload) => (string) $payload['water_id']);

        return view('venues.show', [
            'venue' => $venue,
            'speciesList' => $venue->allSpecies(),
            'anglerTactics' => $venue->anglerTactics,
            'pendingPegs' => $venue->waters->flatMap->pegs->where('is_verified', false)->values(),
            'pegMapPayloads' => $pegMapPayloads,
            'isFavourited' => auth()->check() && auth()->user()->hasFavourited($venue),
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
