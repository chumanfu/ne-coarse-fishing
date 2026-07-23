<?php

namespace App\Http\Controllers;

use App\Models\FishingSession;
use App\Models\SessionCatch;
use App\Models\SessionPhoto;
use App\Models\Species;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FishingSessionController extends Controller
{
    public function index(Request $request): View
    {
        $sessions = FishingSession::query()
            ->with(['venue', 'water', 'catches.species', 'photos'])
            ->where('user_id', $request->user()->id)
            ->latest('fished_at')
            ->paginate(10);

        return view('sessions.index', compact('sessions'));
    }

    public function create(Request $request): View
    {
        $venue = null;

        if ($request->filled('venue')) {
            $venue = Venue::query()->approved()->where('slug', $request->string('venue'))->firstOrFail();
            $venue->load('waters');
        }

        return view('sessions.create', [
            'venue' => $venue,
            'venues' => Venue::approved()->orderBy('name')->get(['id', 'name', 'slug']),
            'species' => Species::orderBy('name')->get(),
            'watersJson' => Venue::approved()->with('waters:id,venue_id,name')->get()
                ->mapWithKeys(fn (Venue $v) => [$v->id => $v->waters->map(fn ($w) => ['id' => $w->id, 'name' => $w->name])]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'venue_id' => ['required', 'exists:venues,id'],
            'water_id' => ['nullable', 'exists:waters,id'],
            'fished_at' => ['required', 'date', 'before_or_equal:today'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:72'],
            'weather' => ['nullable', 'string', 'max:255'],
            'peg_number' => ['nullable', 'string', 'max:50'],
            'commentary' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'max:5120'],
            'catches' => ['nullable', 'array'],
            'catches.*.species_id' => ['required_with:catches', 'exists:species,id'],
            'catches.*.weight_lb' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'catches.*.bait' => ['nullable', 'string', 'max:255'],
            'catches.*.quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $venue = Venue::approved()->findOrFail($validated['venue_id']);

        if (! empty($validated['water_id'])) {
            abort_unless($venue->waters()->whereKey($validated['water_id'])->exists(), 422);
        }

        $session = DB::transaction(function () use ($validated, $request, $venue) {
            $session = FishingSession::create([
                'user_id' => $request->user()->id,
                'venue_id' => $venue->id,
                'water_id' => $validated['water_id'] ?? null,
                'fished_at' => $validated['fished_at'],
                'duration_hours' => $validated['duration_hours'] ?? null,
                'weather' => $validated['weather'] ?? null,
                'peg_number' => $validated['peg_number'] ?? null,
                'commentary' => $validated['commentary'] ?? null,
            ]);

            foreach ($validated['catches'] ?? [] as $catch) {
                if (empty($catch['species_id'])) {
                    continue;
                }

                $session->catches()->create([
                    'species_id' => $catch['species_id'],
                    'weight_lb' => $catch['weight_lb'] ?? null,
                    'bait' => $catch['bait'] ?? null,
                    'quantity' => $catch['quantity'] ?? 1,
                ]);
            }

            foreach ($request->file('photos', []) as $photo) {
                $path = $photo->store('session-photos', 'public');
                $session->photos()->create(['image_path' => $path]);
            }

            return $session;
        });

        return redirect()
            ->route('sessions.show', $session)
            ->with('status', 'Session logged. Tight lines!');
    }

    public function show(FishingSession $fishingSession): View
    {
        $this->authorizeSession($fishingSession);

        $fishingSession->load(['venue', 'water', 'user', 'catches.species', 'photos']);

        return view('sessions.show', ['session' => $fishingSession]);
    }

    public function destroy(FishingSession $fishingSession): RedirectResponse
    {
        $this->authorizeSession($fishingSession);

        $fishingSession->photos->each(function (SessionPhoto $photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->image_path);
            $photo->delete();
        });

        $fishingSession->catches()->delete();
        $fishingSession->delete();

        return redirect()->route('sessions.index')->with('status', 'Session deleted.');
    }

    private function authorizeSession(FishingSession $session): void
    {
        $user = request()->user();
        abort_unless($user && ($session->user_id === $user->id || $user->hasRole('super_admin')), 403);
    }
}
