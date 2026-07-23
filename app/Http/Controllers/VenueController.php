<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\Venue;
use App\Models\Water;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(Request $request): View
    {
        $venues = Venue::query()
            ->approved()
            ->with(['waters.species', 'manager'])
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

        return view('venues.create', [
            'species' => Species::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Venue::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'directions' => ['nullable', 'string'],
            'day_ticket_info' => ['nullable', 'string'],
            'membership_info' => ['nullable', 'string'],
            'ticket_type' => ['required', 'in:day_ticket,club,syndicate,mixed'],
            'opening_times' => ['nullable', 'string'],
            'season_info' => ['nullable', 'string'],
            'tactics_guide' => ['nullable', 'string'],
            'is_complex' => ['sometimes', 'boolean'],
            'waters' => ['required', 'array', 'min:1'],
            'waters.*.name' => ['required', 'string', 'max:255'],
            'waters.*.description' => ['nullable', 'string'],
            'waters.*.peg_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'waters.*.depth_info' => ['nullable', 'string'],
            'waters.*.species' => ['nullable', 'array'],
            'waters.*.species.*' => ['integer', 'exists:species,id'],
        ]);

        $venue = DB::transaction(function () use ($validated, $request) {
            $venue = Venue::create([
                'user_id' => $request->user()->id,
                'name' => $validated['name'],
                'overview' => $validated['overview'] ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'address' => $validated['address'] ?? null,
                'directions' => $validated['directions'] ?? null,
                'day_ticket_info' => $validated['day_ticket_info'] ?? null,
                'membership_info' => $validated['membership_info'] ?? null,
                'ticket_type' => $validated['ticket_type'],
                'opening_times' => $validated['opening_times'] ?? null,
                'season_info' => $validated['season_info'] ?? null,
                'tactics_guide' => $validated['tactics_guide'] ?? null,
                'is_complex' => $request->boolean('is_complex') || count($validated['waters']) > 1,
                'is_approved' => false,
            ]);

            foreach ($validated['waters'] as $index => $waterData) {
                $water = $venue->waters()->create([
                    'name' => $waterData['name'],
                    'description' => $waterData['description'] ?? null,
                    'peg_count' => $waterData['peg_count'] ?? null,
                    'depth_info' => $waterData['depth_info'] ?? null,
                    'sort_order' => $index,
                ]);

                if (! empty($waterData['species'])) {
                    $water->species()->sync($waterData['species']);
                }
            }

            return $venue;
        });

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Venue submitted for approval. Thanks for helping map the North East!');
    }

    public function show(Venue $venue): View
    {
        $this->authorize('view', $venue);

        $venue->load([
            'waters.species',
            'manager',
            'matchReports' => fn ($q) => $q->whereNotNull('published_at')->latest('published_at')->limit(10),
            'announcements' => fn ($q) => $q->whereNotNull('published_at')->latest('published_at')->limit(10),
            'fishingSessions' => fn ($q) => $q->with(['user', 'catches.species', 'photos'])->latest('fished_at')->limit(8),
        ]);

        return view('venues.show', [
            'venue' => $venue,
            'speciesList' => $venue->allSpecies(),
        ]);
    }

    public function edit(Venue $venue): View
    {
        $this->authorize('manage', $venue);

        $venue->load('waters.species');

        return view('venues.edit', [
            'venue' => $venue,
            'species' => Species::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        $this->authorize('manage', $venue);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'directions' => ['nullable', 'string'],
            'day_ticket_info' => ['nullable', 'string'],
            'membership_info' => ['nullable', 'string'],
            'ticket_type' => ['required', 'in:day_ticket,club,syndicate,mixed'],
            'opening_times' => ['nullable', 'string'],
            'season_info' => ['nullable', 'string'],
            'tactics_guide' => ['nullable', 'string'],
            'is_complex' => ['sometimes', 'boolean'],
            'waters' => ['required', 'array', 'min:1'],
            'waters.*.id' => ['nullable', 'integer'],
            'waters.*.name' => ['required', 'string', 'max:255'],
            'waters.*.description' => ['nullable', 'string'],
            'waters.*.peg_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'waters.*.depth_info' => ['nullable', 'string'],
            'waters.*.species' => ['nullable', 'array'],
            'waters.*.species.*' => ['integer', 'exists:species,id'],
        ]);

        DB::transaction(function () use ($validated, $request, $venue) {
            $venue->update([
                'name' => $validated['name'],
                'overview' => $validated['overview'] ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'address' => $validated['address'] ?? null,
                'directions' => $validated['directions'] ?? null,
                'day_ticket_info' => $validated['day_ticket_info'] ?? null,
                'membership_info' => $validated['membership_info'] ?? null,
                'ticket_type' => $validated['ticket_type'],
                'opening_times' => $validated['opening_times'] ?? null,
                'season_info' => $validated['season_info'] ?? null,
                'tactics_guide' => $validated['tactics_guide'] ?? null,
                'is_complex' => $request->boolean('is_complex') || count($validated['waters']) > 1,
            ]);

            $keepIds = [];

            foreach ($validated['waters'] as $index => $waterData) {
                $water = null;

                if (! empty($waterData['id'])) {
                    $water = $venue->waters()->whereKey($waterData['id'])->first();
                }

                $payload = [
                    'name' => $waterData['name'],
                    'description' => $waterData['description'] ?? null,
                    'peg_count' => $waterData['peg_count'] ?? null,
                    'depth_info' => $waterData['depth_info'] ?? null,
                    'sort_order' => $index,
                ];

                if ($water) {
                    $water->update($payload);
                } else {
                    $water = $venue->waters()->create($payload);
                }

                $water->species()->sync($waterData['species'] ?? []);
                $keepIds[] = $water->id;
            }

            $venue->waters()->whereNotIn('id', $keepIds)->each(function (Water $water) {
                $water->species()->detach();
                $water->delete();
            });
        });

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Venue details updated.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        $this->authorize('delete', $venue);
        $venue->delete();

        return redirect()->route('venues.index')->with('status', 'Venue deleted.');
    }
}
