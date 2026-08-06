<?php

namespace App\Http\Controllers;

use App\Models\FishingSession;
use App\Models\SessionPhoto;
use App\Models\Species;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Services\ActivityLogger;
use App\Services\VenueTacticService;
use App\Services\WaterPegService;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FishingSessionController extends Controller
{
    public function index(Request $request): View
    {
        $sessions = FishingSession::query()
            ->with(['venue', 'water', 'waterPeg', 'catches.species', 'photos', 'venueTactic'])
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

        return view('sessions.create', $this->formData($venue, null, $request->user()));
    }

    public function store(
        Request $request,
        VenueTacticService $tactics,
        WaterPegService $pegs,
        ActivityLogger $activities,
    ): RedirectResponse {
        $validated = $this->validatedSession($request);
        $venue = Venue::approved()->findOrFail($validated['venue_id']);
        $this->assertWaterBelongsToVenue($venue, $validated['water_id'] ?? null);

        $session = DB::transaction(function () use ($validated, $request, $venue, $tactics, $pegs) {
            $pegData = $this->resolvePeg($validated, $venue, $request->user(), $pegs, $request->file('peg_photos', []));

            $session = FishingSession::create([
                'user_id' => $request->user()->id,
                'venue_id' => $venue->id,
                'water_id' => $validated['water_id'] ?? $pegData['water_id'] ?? null,
                'water_peg_id' => $pegData['water_peg_id'],
                'fished_at' => $validated['fished_at'],
                'duration_hours' => $validated['duration_hours'] ?? null,
                'weather' => $validated['weather'] ?? null,
                'peg_number' => $pegData['peg_number'],
                'peg_latitude' => $pegData['peg_latitude'],
                'peg_longitude' => $pegData['peg_longitude'],
                'commentary' => $validated['commentary'] ?? null,
                'tactics_tip' => filled($validated['tactics_tip'] ?? null) ? trim($validated['tactics_tip']) : null,
            ]);

            $this->syncCatches($session, $validated['catches'] ?? []);
            $this->storePhotos($request, $session);
            $tactics->syncFromSession($session, $validated['tactics_tip'] ?? null);

            return $session;
        });

        $activities->sessionLogged($session->load('venue', 'user'));

        return redirect()
            ->route('sessions.show', $session)
            ->with('status', 'Session logged. Tight lines!');
    }

    public function show(FishingSession $fishingSession): View
    {
        $this->authorizeView($fishingSession);

        $fishingSession->load(['venue', 'water', 'waterPeg.photos', 'user', 'catches.species', 'photos', 'venueTactic']);

        return view('sessions.show', [
            'session' => $fishingSession,
            'canManage' => $this->canManage($fishingSession),
        ]);
    }

    public function edit(FishingSession $fishingSession): View
    {
        $this->authorizeManage($fishingSession);

        $fishingSession->load(['venue.waters', 'water', 'waterPeg', 'catches', 'venueTactic', 'photos']);

        return view('sessions.create', $this->formData($fishingSession->venue, $fishingSession, request()->user()));
    }

    public function update(
        Request $request,
        FishingSession $fishingSession,
        VenueTacticService $tactics,
        WaterPegService $pegs,
    ): RedirectResponse {
        $this->authorizeManage($fishingSession);

        $validated = $this->validatedSession($request, $fishingSession);
        $venue = Venue::approved()->findOrFail($validated['venue_id']);
        $this->assertWaterBelongsToVenue($venue, $validated['water_id'] ?? null);

        DB::transaction(function () use ($validated, $request, $fishingSession, $tactics, $pegs, $venue) {
            $pegData = $this->resolvePeg($validated, $venue, $request->user(), $pegs, $request->file('peg_photos', []));

            $fishingSession->update([
                'venue_id' => $validated['venue_id'],
                'water_id' => $validated['water_id'] ?? $pegData['water_id'] ?? null,
                'water_peg_id' => $pegData['water_peg_id'],
                'fished_at' => $validated['fished_at'],
                'duration_hours' => $validated['duration_hours'] ?? null,
                'weather' => $validated['weather'] ?? null,
                'peg_number' => $pegData['peg_number'],
                'peg_latitude' => $pegData['peg_latitude'],
                'peg_longitude' => $pegData['peg_longitude'],
                'commentary' => $validated['commentary'] ?? null,
                'tactics_tip' => filled($validated['tactics_tip'] ?? null) ? trim($validated['tactics_tip']) : null,
            ]);

            $fishingSession->catches()->delete();
            $this->syncCatches($fishingSession, $validated['catches'] ?? []);
            $this->removePhotos($request, $fishingSession);
            $this->storePhotos($request, $fishingSession);
            $tactics->syncFromSession($fishingSession->fresh(), $validated['tactics_tip'] ?? null);
        });

        return redirect()
            ->route('sessions.show', $fishingSession)
            ->with('status', 'Session updated.');
    }

    public function destroy(FishingSession $fishingSession): RedirectResponse
    {
        $this->authorizeManage($fishingSession);

        $fishingSession->venueTactic?->delete();

        $fishingSession->photos->each(function (SessionPhoto $photo) {
            Uploads::delete($photo->image_path);
            $photo->delete();
        });

        $fishingSession->catches()->delete();
        $fishingSession->delete();

        return redirect()->route('sessions.index')->with('status', 'Session deleted.');
    }

    /** @return array<string, mixed> */
    private function formData(?Venue $venue, ?FishingSession $session, $user): array
    {
        $pegsJson = Venue::approved()
            ->with(['waters.pegs' => fn ($q) => $q->visibleTo($user)->orderBy('sort_order')->orderBy('id')])
            ->get()
            ->mapWithKeys(function (Venue $v) {
                return [(string) $v->id => $v->waters->mapWithKeys(function (Water $water) {
                    return [(string) $water->id => $water->pegs->map(fn (WaterPeg $peg) => [
                        'id' => $peg->id,
                        'water_id' => $water->id,
                        'label' => $peg->label(),
                        'name' => $peg->name,
                        'number' => $peg->number,
                        'lat' => $peg->latitude,
                        'lng' => $peg->longitude,
                        'verified' => $peg->is_verified,
                    ])->values()->all()];
                })->all()];
            })
            ->all();

        // Always include the session's linked peg so edit can recall it even if it
        // would otherwise be filtered out of the visible peg list.
        if ($session?->waterPeg) {
            $peg = $session->waterPeg;
            $venueKey = (string) $session->venue_id;
            $waterKey = (string) $peg->water_id;
            $pegsJson[$venueKey] ??= [];
            $pegsJson[$venueKey][$waterKey] ??= [];

            $alreadyListed = collect($pegsJson[$venueKey][$waterKey])
                ->contains(fn (array $item) => (int) $item['id'] === (int) $peg->id);

            if (! $alreadyListed) {
                $pegsJson[$venueKey][$waterKey][] = [
                    'id' => $peg->id,
                    'water_id' => $peg->water_id,
                    'label' => $peg->label(),
                    'name' => $peg->name,
                    'number' => $peg->number,
                    'lat' => $peg->latitude ?? $session->peg_latitude,
                    'lng' => $peg->longitude ?? $session->peg_longitude,
                    'verified' => $peg->is_verified,
                ];
            }
        }

        return [
            'venue' => $venue,
            'session' => $session,
            'venues' => Venue::approved()->orderBy('name')->get(['id', 'name', 'slug', 'latitude', 'longitude']),
            'species' => Species::orderBy('name')->get(),
            'watersJson' => Venue::approved()->with('waters:id,venue_id,name')->get()
                ->mapWithKeys(fn (Venue $v) => [(string) $v->id => $v->waters->map(fn ($w) => ['id' => $w->id, 'name' => $w->name])->values()->all()])
                ->all(),
            'venuesJson' => Venue::approved()->orderBy('name')->get(['id', 'name', 'latitude', 'longitude'])
                ->mapWithKeys(fn (Venue $v) => [(string) $v->id => [
                    'name' => $v->name,
                    'lat' => (float) $v->latitude,
                    'lng' => (float) $v->longitude,
                ]])
                ->all(),
            'pegsJson' => $pegsJson,
        ];
    }

    /** @return array<string, mixed> */
    private function validatedSession(Request $request, ?FishingSession $session = null): array
    {
        if (in_array($request->input('water_id'), ['all', ''], true)) {
            $request->merge(['water_id' => null]);
        }

        return $request->validate([
            'venue_id' => ['required', 'exists:venues,id'],
            'water_id' => ['nullable', 'exists:waters,id'],
            'water_peg_id' => ['nullable', 'integer', 'exists:water_pegs,id'],
            'peg_mode' => ['nullable', Rule::in(['existing', 'new', 'none'])],
            'fished_at' => ['required', 'date', 'before_or_equal:today'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:72'],
            'weather' => ['nullable', 'string', 'max:255'],
            'peg_number' => ['nullable', 'string', 'max:50'],
            'peg_name' => ['nullable', 'string', 'max:100'],
            'peg_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:peg_longitude'],
            'peg_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:peg_latitude'],
            'peg_photos' => ['nullable', 'array', 'max:4'],
            'peg_photos.*' => ['image', 'max:5120'],
            'commentary' => ['nullable', 'string'],
            'tactics_tip' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'max:5120'],
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => [
                'integer',
                Rule::exists('session_photos', 'id')->where(
                    fn ($query) => $session
                        ? $query->where('fishing_session_id', $session->id)
                        : $query->whereRaw('0 = 1')
                ),
            ],
            'catches' => ['nullable', 'array'],
            'catches.*.species_id' => ['required_with:catches', 'exists:species,id'],
            'catches.*.weight_lb' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'catches.*.bait' => ['nullable', 'string', 'max:255'],
            'catches.*.quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  list<\Illuminate\Http\UploadedFile>|array<\Illuminate\Http\UploadedFile>|null  $pegPhotos
     * @return array{water_peg_id: ?int, water_id: ?int, peg_number: ?string, peg_latitude: ?float, peg_longitude: ?float}
     */
    private function resolvePeg(array $validated, Venue $venue, $user, WaterPegService $pegs, array $pegPhotos = []): array
    {
        $mode = $validated['peg_mode'] ?? 'none';

        if ($mode === 'existing' && ! empty($validated['water_peg_id'])) {
            $peg = WaterPeg::query()
                ->visibleTo($user)
                ->whereKey($validated['water_peg_id'])
                ->whereHas('water', fn ($q) => $q->where('venue_id', $venue->id))
                ->firstOrFail();

            if (! empty($validated['water_id'])) {
                abort_unless((int) $peg->water_id === (int) $validated['water_id'], 422);
            }

            return [
                'water_peg_id' => $peg->id,
                'water_id' => $peg->water_id,
                'peg_number' => $peg->label(),
                'peg_latitude' => $peg->latitude,
                'peg_longitude' => $peg->longitude,
            ];
        }

        if ($mode === 'new') {
            abort_unless(! empty($validated['water_id']), 422, 'Choose a water before adding a new peg.');
            abort_unless(isset($validated['peg_latitude'], $validated['peg_longitude']), 422, 'Mark the new peg on the map.');

            $water = $venue->waters()->whereKey($validated['water_id'])->firstOrFail();
            $peg = $pegs->createForWater($water, $user, [
                'name' => $validated['peg_name'] ?? null,
                'number' => $validated['peg_number'] ?? null,
                'latitude' => $validated['peg_latitude'],
                'longitude' => $validated['peg_longitude'],
            ], false, array_values(array_filter($pegPhotos)));

            return [
                'water_peg_id' => $peg->id,
                'water_id' => $peg->water_id,
                'peg_number' => $peg->label(),
                'peg_latitude' => $peg->latitude,
                'peg_longitude' => $peg->longitude,
            ];
        }

        // Legacy / freeform map pin without official peg.
        return [
            'water_peg_id' => null,
            'water_id' => $validated['water_id'] ?? null,
            'peg_number' => $validated['peg_number'] ?? null,
            'peg_latitude' => $validated['peg_latitude'] ?? null,
            'peg_longitude' => $validated['peg_longitude'] ?? null,
        ];
    }

    private function assertWaterBelongsToVenue(Venue $venue, mixed $waterId): void
    {
        if (! empty($waterId)) {
            abort_unless($venue->waters()->whereKey($waterId)->exists(), 422);
        }
    }

    /** @param  list<array<string, mixed>>  $catches */
    private function syncCatches(FishingSession $session, array $catches): void
    {
        foreach ($catches as $catch) {
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
    }

    private function removePhotos(Request $request, FishingSession $session): void
    {
        $ids = collect($request->input('remove_photo_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return;
        }

        $session->photos()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (SessionPhoto $photo) {
                Uploads::delete($photo->image_path);
                $photo->delete();
            });
    }

    private function storePhotos(Request $request, FishingSession $session): void
    {
        foreach ($request->file('photos', []) as $photo) {
            $path = Uploads::store($photo, 'session-photos');

            $session->photos()->create(['image_path' => $path]);
        }
    }

    private function authorizeView(FishingSession $session): void
    {
        $session->loadMissing('venue');

        abort_unless($session->venue?->is_approved || $this->canManage($session), 404);
    }

    private function authorizeManage(FishingSession $session): void
    {
        abort_unless($this->canManage($session), 403);
    }

    private function canManage(FishingSession $session): bool
    {
        $user = request()->user();

        return (bool) ($user && ($session->user_id === $user->id || $user->hasRole('super_admin')));
    }
}
