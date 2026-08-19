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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            $pegData = $this->resolvePeg($validated, $venue, $request->user(), $pegs, $this->validUploads($request, 'peg_photos'));

            $session = FishingSession::create([
                'user_id' => $request->user()->id,
                'venue_id' => $venue->id,
                'water_id' => $pegData['water_id'] ?? $validated['water_id'] ?? null,
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
            $pegData = $this->resolvePeg($validated, $venue, $request->user(), $pegs, $this->validUploads($request, 'peg_photos'));

            $fishingSession->update([
                'venue_id' => $validated['venue_id'],
                'water_id' => $pegData['water_id'] ?? $validated['water_id'] ?? null,
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
            ->with([
                'waters' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
                'waters.pegs' => fn ($q) => $q->visibleTo($user)->orderBy('sort_order')->orderBy('id'),
            ])
            ->get()
            ->mapWithKeys(function (Venue $v) {
                return [(string) $v->id => $v->waters->mapWithKeys(function (Water $water) {
                    return [(string) $water->id => $water->pegs->map(fn (WaterPeg $peg) => [
                        'id' => $peg->id,
                        'water_id' => $water->id,
                        'label' => $peg->label(),
                        'name' => $peg->name,
                        'number' => $peg->number,
                        'x' => $peg->map_x,
                        'y' => $peg->map_y,
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
                    'x' => $peg->map_x,
                    'y' => $peg->map_y,
                    'verified' => $peg->is_verified,
                ];
            }
        }

        return [
            'venue' => $venue,
            'session' => $session,
            'venues' => Venue::approved()->orderBy('name')->get(['id', 'name', 'slug', 'latitude', 'longitude']),
            'species' => Species::orderBy('name')->get(),
            'watersJson' => Venue::approved()->with('waters:id,venue_id,name,map_image_path')->get()
                ->mapWithKeys(fn (Venue $v) => [(string) $v->id => $v->waters->map(fn ($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                    'map_url' => $w->mapImageUrl(),
                ])->values()->all()])
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

        $pegMode = $request->input('peg_mode', 'none');

        if ($pegMode !== 'existing') {
            $request->merge(['water_peg_id' => null]);
        }

        if ($pegMode !== 'new') {
            $request->merge([
                'peg_name' => null,
                'peg_map_x' => null,
                'peg_map_y' => null,
            ]);
            $request->files->remove('peg_photos');
        }

        $this->forgetEmptyUploads($request, ['photos', 'peg_photos']);

        // The form always posts at least one catch row; drop blank ones before validating.
        $request->merge([
            'catches' => collect($request->input('catches', []))
                ->filter(fn ($catch) => filled($catch['species_id'] ?? null))
                ->values()
                ->all() ?: null,
        ]);

        $addingPeg = $request->input('peg_mode') === 'new';

        return $request->validate([
            'venue_id' => ['required', 'exists:venues,id'],
            'water_id' => [
                Rule::requiredIf($addingPeg),
                'nullable',
                Rule::exists('waters', 'id')->where(
                    fn ($query) => $query->where('venue_id', $request->input('venue_id'))
                ),
            ],
            'water_peg_id' => ['nullable', 'integer', 'exists:water_pegs,id'],
            'peg_mode' => ['nullable', Rule::in(['existing', 'new', 'none'])],
            'fished_at' => ['required', 'date', 'before_or_equal:today'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:72'],
            'weather' => ['nullable', 'string', 'max:255'],
            'peg_number' => ['nullable', 'string', 'max:50'],
            'peg_name' => ['nullable', 'string', 'max:100'],
            'peg_map_x' => [
                Rule::requiredIf($addingPeg),
                'nullable',
                'numeric',
                'between:0,100',
                'required_with:peg_map_y',
            ],
            'peg_map_y' => [
                Rule::requiredIf($addingPeg),
                'nullable',
                'numeric',
                'between:0,100',
                'required_with:peg_map_x',
            ],
            'peg_photos' => ['nullable', 'array', 'max:4'],
            'peg_photos.*' => ['nullable', 'image', 'max:5120'],
            'commentary' => ['nullable', 'string'],
            'tactics_tip' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['nullable', 'image', 'max:5120'],
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
            'catches.*.species_id' => ['required', 'exists:species,id'],
            'catches.*.weight_lb' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'catches.*.bait' => ['nullable', 'string', 'max:255'],
            'catches.*.quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'water_id.required' => 'Choose a water before adding a new peg.',
            'peg_map_x.required' => 'Mark the new peg on the pond map.',
            'peg_map_y.required' => 'Mark the new peg on the pond map.',
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
                ->first();

            if (! $peg) {
                throw ValidationException::withMessages([
                    'water_peg_id' => 'Select a peg from this venue, or add a new one.',
                ]);
            }

            return [
                'water_peg_id' => $peg->id,
                'water_id' => $peg->water_id,
                'peg_number' => $peg->label(),
                'peg_latitude' => null,
                'peg_longitude' => null,
            ];
        }

        if ($mode === 'new') {
            if (empty($validated['water_id'])) {
                throw ValidationException::withMessages([
                    'water_id' => 'Choose a water before adding a new peg.',
                ]);
            }

            if (! isset($validated['peg_map_x'], $validated['peg_map_y'])) {
                throw ValidationException::withMessages([
                    'peg_map_x' => 'Mark the new peg on the pond map.',
                    'peg_map_y' => 'Mark the new peg on the pond map.',
                ]);
            }

            $water = $venue->waters()->whereKey($validated['water_id'])->first();
            if (! $water) {
                throw ValidationException::withMessages([
                    'water_id' => 'Choose a water that belongs to this venue.',
                ]);
            }

            if (! $water->hasMapImage()) {
                throw ValidationException::withMessages([
                    'water_id' => 'This water needs a pond map image before pegs can be placed.',
                ]);
            }

            $peg = $pegs->createForWater($water, $user, [
                'name' => $validated['peg_name'] ?? null,
                'number' => $validated['peg_number'] ?? null,
                'map_x' => $validated['peg_map_x'],
                'map_y' => $validated['peg_map_y'],
            ], false, array_values(array_filter($pegPhotos)));

            return [
                'water_peg_id' => $peg->id,
                'water_id' => $peg->water_id,
                'peg_number' => $peg->label(),
                'peg_latitude' => null,
                'peg_longitude' => null,
            ];
        }

        return [
            'water_peg_id' => null,
            'water_id' => $validated['water_id'] ?? null,
            'peg_number' => $validated['peg_number'] ?? null,
            'peg_latitude' => null,
            'peg_longitude' => null,
        ];
    }

    private function assertWaterBelongsToVenue(Venue $venue, mixed $waterId): void
    {
        if (! empty($waterId) && ! $venue->waters()->whereKey($waterId)->exists()) {
            throw ValidationException::withMessages([
                'water_id' => 'Choose a water that belongs to this venue.',
            ]);
        }
    }

    /** @param  list<string>  $keys */
    private function forgetEmptyUploads(Request $request, array $keys): void
    {
        $newBag = [];

        foreach ($request->files->all() as $key => $files) {
            if (! in_array($key, $keys, true)) {
                $newBag[$key] = $files;

                continue;
            }

            if (! is_array($files)) {
                $files = $files ? [$files] : [];
            }

            $valid = array_values(array_filter(
                $files,
                fn ($file) => $file instanceof SymfonyUploadedFile && $file->isValid(),
            ));

            if ($valid !== []) {
                $newBag[$key] = $valid;
            }
        }

        $request->files->replace($newBag);
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
        foreach ($this->validUploads($request, 'photos') as $photo) {
            $path = Uploads::store($photo, 'session-photos');

            $session->photos()->create(['image_path' => $path]);
        }
    }

    /** @return list<UploadedFile> */
    private function validUploads(Request $request, string $key): array
    {
        $files = $request->file($key, []);

        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            fn ($file) => $file instanceof UploadedFile && $file->isValid(),
        ));
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
