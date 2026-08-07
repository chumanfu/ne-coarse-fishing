<?php

namespace App\Livewire;

use App\Filament\Resources\Venues\VenueResource;
use App\Models\Species;
use App\Models\Venue;
use App\Models\VenueEditRequest;
use App\Models\VenuePhoto;
use App\Models\Water;
use App\Services\GeocodingService;
use App\Services\VenuePersistenceService;
use App\Support\Uploads;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class VenueWizard extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?int $venueId = null;

    public bool $admin = false;

    public bool $editRequest = false;

    public string $editRequestMessage = '';

    public int $step = 1;

    public string $searchQuery = '';

    /** @var list<array{display_name: string, latitude: float, longitude: float, address: string}> */
    public array $searchResults = [];

    public ?string $searchError = null;

    public float $latitude = 54.7767;

    public float $longitude = -1.5753;

    public bool $locationSet = false;

    public string $name = '';

    public string $slugPreview = '';

    public string $overview = '';

    public string $address = '';

    public string $url = '';

    public string $facebookUrl = '';

    public string $what3words = '';

    public string $directions = '';

    public string $ticket_type = 'day_ticket';

    public string $day_ticket_info = '';

    public string $membership_info = '';

    public string $opening_times = '';

    public string $season_info = '';

    public string $tactics_guide = '';

    public bool $is_complex = false;

    public bool $is_approved = false;

    public bool $manager_verified = false;

    /** @var list<array{id: ?int, name: string, description: string, peg_count: mixed, depth_info: string, species: list<int|string>, pegs: list<array{id: ?int, name: string, number: string, latitude: mixed, longitude: mixed}>}> */
    public array $waters = [];

    /** @var list<int> */
    public array $existingPhotoIds = [];

    /** @var list<TemporaryUploadedFile> */
    public array $newPhotos = [];

    public function mount(mixed $venue = null, bool $admin = false, bool $editRequest = false): void
    {
        $this->admin = $admin || auth()->user()?->hasRole('super_admin') === true;
        $this->editRequest = $editRequest;

        $this->waters = [[
            'id' => null,
            'name' => '',
            'description' => '',
            'peg_count' => '',
            'depth_info' => '',
            'species' => [],
            'facilities' => [],
            'pegs' => [],
        ]];

        $model = $this->resolveVenueModel($venue);

        if ($model) {
            if ($this->editRequest) {
                $this->authorize('suggestEdit', $model);
            } else {
                $this->authorizeEdit($model);
            }
            $this->venueId = $model->id;
            $this->fillFromVenue($model);
        } else {
            $this->authorize('create', Venue::class);
        }
    }

    public function getVenueProperty(): ?Venue
    {
        if (! $this->venueId) {
            return null;
        }

        return Venue::query()->find($this->venueId);
    }

    public function updatedName(string $value): void
    {
        if (blank($value)) {
            $this->slugPreview = '';

            return;
        }

        $this->slugPreview = Venue::uniqueSlug($value, $this->venueId);
    }

    public function updatedWhat3words(?string $value): void
    {
        $this->what3words = Venue::normalizeWhat3words($value) ?? '';
    }

    public function getExistingPhotosProperty()
    {
        if ($this->existingPhotoIds === []) {
            return collect();
        }

        return VenuePhoto::query()
            ->whereIn('id', $this->existingPhotoIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function searchLocation(GeocodingService $geocoding): void
    {
        $this->searchError = null;
        $this->validate([
            'searchQuery' => ['required', 'string', 'max:255'],
        ]);

        $this->searchResults = $geocoding->search($this->searchQuery);

        if ($this->searchResults === []) {
            $this->searchError = 'No places found. Try a UK postcode, place name, or lat,lng.';
        }
    }

    public function selectSearchResult(int $index, GeocodingService $geocoding): void
    {
        $result = $this->searchResults[$index] ?? null;

        if (! $result) {
            return;
        }

        $this->setLocation($result['latitude'], $result['longitude'], $result['address'] ?: null, $geocoding);
        $this->searchResults = [];
        $this->searchQuery = $result['display_name'];
    }

    public function setPin(float $latitude, float $longitude, GeocodingService $geocoding): void
    {
        $this->setLocation($latitude, $longitude, null, $geocoding);
    }

    public function reverseGeocode(GeocodingService $geocoding): void
    {
        if (! $this->locationSet) {
            return;
        }

        $result = $geocoding->reverse($this->latitude, $this->longitude);

        if ($result['address'] !== '') {
            $this->address = $result['address'];
        }
    }

    public function updatedLatitude(): void
    {
        if ($this->latitude !== null) {
            $this->locationSet = true;
        }
    }

    public function updatedLongitude(): void
    {
        if ($this->longitude !== null) {
            $this->locationSet = true;
        }
    }

    public function nextStep(): void
    {
        $this->validateStep($this->step);

        $this->step = min(5, $this->step + 1);

        if ($this->step === 4 && blank($this->address)) {
            $this->reverseGeocode(app(GeocodingService::class));
        }

        if ($this->step === 5) {
            $this->dispatch('peg-maps-refresh');
        }
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 5 || $step > $this->step) {
            return;
        }

        $this->step = $step;

        if ($this->step === 5) {
            $this->dispatch('peg-maps-refresh');
        }
    }

    public function addWater(): void
    {
        $this->waters[] = [
            'id' => null,
            'name' => '',
            'description' => '',
            'peg_count' => '',
            'depth_info' => '',
            'species' => [],
            'facilities' => [],
            'pegs' => [],
        ];
        $this->is_complex = true;
    }

    public function addPeg(int $waterIndex): void
    {
        if (! isset($this->waters[$waterIndex])) {
            return;
        }

        $this->waters[$waterIndex]['pegs'][] = [
            'id' => null,
            'name' => '',
            'number' => '',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'existing_photo_ids' => [],
            'existing_photos' => [],
            'new_photos' => [],
        ];

        $this->dispatch('peg-maps-refresh');
    }

    public function removePeg(int $waterIndex, int $pegIndex): void
    {
        if (! isset($this->waters[$waterIndex]['pegs'][$pegIndex])) {
            return;
        }

        unset($this->waters[$waterIndex]['pegs'][$pegIndex]);
        $this->waters[$waterIndex]['pegs'] = array_values($this->waters[$waterIndex]['pegs']);
        $this->dispatch('peg-maps-refresh');
    }

    public function removePegExistingPhoto(int $waterIndex, int $pegIndex, int $photoId): void
    {
        if (! isset($this->waters[$waterIndex]['pegs'][$pegIndex])) {
            return;
        }

        $peg = &$this->waters[$waterIndex]['pegs'][$pegIndex];
        $peg['existing_photo_ids'] = array_values(array_filter(
            $peg['existing_photo_ids'] ?? [],
            fn ($id): bool => (int) $id !== $photoId
        ));
        $peg['existing_photos'] = array_values(array_filter(
            $peg['existing_photos'] ?? [],
            fn (array $photo): bool => (int) $photo['id'] !== $photoId
        ));
    }

    public function removePegNewPhoto(int $waterIndex, int $pegIndex, int $photoIndex): void
    {
        if (! isset($this->waters[$waterIndex]['pegs'][$pegIndex]['new_photos'][$photoIndex])) {
            return;
        }

        unset($this->waters[$waterIndex]['pegs'][$pegIndex]['new_photos'][$photoIndex]);
        $this->waters[$waterIndex]['pegs'][$pegIndex]['new_photos'] = array_values(
            $this->waters[$waterIndex]['pegs'][$pegIndex]['new_photos']
        );
    }

    public function setPegLocation(int $waterIndex, int $pegIndex, float $latitude, float $longitude): void
    {
        if (! isset($this->waters[$waterIndex]['pegs'][$pegIndex])) {
            return;
        }

        $this->waters[$waterIndex]['pegs'][$pegIndex]['latitude'] = round($latitude, 7);
        $this->waters[$waterIndex]['pegs'][$pegIndex]['longitude'] = round($longitude, 7);
    }

    public function removeWater(int $index): void
    {
        if (count($this->waters) <= 1) {
            return;
        }

        unset($this->waters[$index]);
        $this->waters = array_values($this->waters);
        $this->is_complex = count($this->waters) > 1;
    }

    public function removeExistingPhoto(int $photoId): void
    {
        $this->existingPhotoIds = array_values(array_filter(
            $this->existingPhotoIds,
            fn (int $id): bool => $id !== $photoId
        ));
    }

    public function removeNewPhoto(int $index): void
    {
        unset($this->newPhotos[$index]);
        $this->newPhotos = array_values($this->newPhotos);
    }

    public function save(): mixed
    {
        foreach (range(1, 5) as $step) {
            $this->validateStep($step);
        }

        if ($this->editRequest) {
            return $this->submitEditRequest();
        }

        $isUpdate = filled($this->venueId);
        $existingVenue = $isUpdate ? Venue::query()->findOrFail($this->venueId) : null;

        if ($existingVenue) {
            $this->authorizeEdit($existingVenue);
        } else {
            $this->authorize('create', Venue::class);
        }

        $venue = DB::transaction(function () use ($existingVenue) {
            $payload = $this->buildVenuePayload();

            if ($this->admin) {
                $payload['is_approved'] = $this->is_approved;
                $payload['manager_verified'] = $this->manager_verified;
            }

            if ($existingVenue) {
                $payload['slug'] = Venue::uniqueSlug($this->name, $existingVenue->id);
                $existingVenue->update($payload);
                $venue = $existingVenue->fresh();
            } else {
                $payload['user_id'] = auth()->id();
                $payload['slug'] = Venue::uniqueSlug($this->name);
                if (! $this->admin) {
                    $payload['is_approved'] = false;
                    $payload['manager_verified'] = false;
                }
                $venue = Venue::create($payload);
                $this->venueId = $venue->id;
            }

            $this->syncWaters($venue);

            if (! $this->editRequest) {
                $this->syncPhotos($venue);
            }

            return $venue;
        });

        $message = $isUpdate
            ? 'Venue details updated.'
            : ($this->admin && $this->is_approved
                ? 'Venue created and approved.'
                : 'Venue submitted for approval. Thanks for helping map the North East!');

        if ($this->admin) {
            return redirect()
                ->to(VenueResource::getUrl('edit', ['record' => $venue->getKey()]))
                ->with('status', $message);
        }

        return redirect()->route('venues.show', $venue)->with('status', $message);
    }

    private function submitEditRequest(): mixed
    {
        $venue = Venue::query()->findOrFail($this->venueId);
        $this->authorize('suggestEdit', $venue);

        $this->validate([
            'editRequestMessage' => ['nullable', 'string', 'max:2000'],
        ]);

        $editRequest = VenueEditRequest::query()->create([
            'venue_id' => $venue->id,
            'user_id' => auth()->id(),
            'message' => filled($this->editRequestMessage) ? trim($this->editRequestMessage) : null,
            'proposed_data' => $this->buildProposedData(),
            'status' => 'pending',
        ]);

        app(\App\Services\ActivityLogger::class)->venueEditSuggested($editRequest);

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', 'Edit suggestion submitted. A fishery manager or admin will review it.');
    }

    /** @return array<string, mixed> */
    private function buildVenuePayload(): array
    {
        return [
            'name' => $this->name,
            'overview' => $this->overview ?: null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address ?: null,
            'url' => $this->url ?: null,
            'facebook_url' => $this->facebookUrl ?: null,
            'what3words' => Venue::normalizeWhat3words($this->what3words),
            'directions' => $this->directions ?: null,
            'day_ticket_info' => $this->day_ticket_info ?: null,
            'membership_info' => $this->membership_info ?: null,
            'ticket_type' => $this->ticket_type,
            'opening_times' => $this->opening_times ?: null,
            'season_info' => $this->season_info ?: null,
            'tactics_guide' => $this->tactics_guide ?: null,
            'is_complex' => $this->is_complex || count($this->waters) > 1,
        ];
    }

    /** @return array{venue: array<string, mixed>, waters: list<array<string, mixed>>} */
    private function buildProposedData(): array
    {
        $venuePayload = collect($this->buildVenuePayload())
            ->only(VenuePersistenceService::VENUE_FIELDS)
            ->all();

        return [
            'venue' => $venuePayload,
            'waters' => collect($this->waters)->map(fn (array $water) => [
                'id' => $water['id'] ?? null,
                'name' => $water['name'],
                'description' => $water['description'] ?: null,
                'peg_count' => $water['peg_count'] !== '' ? $water['peg_count'] : null,
                'depth_info' => $water['depth_info'] ?: null,
                'species' => collect($water['species'] ?? [])
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all(),
                'facilities' => Water::normalizeFacilities($water['facilities'] ?? []),
            ])->values()->all(),
        ];
    }

    private function syncWaters(Venue $venue): void
    {
        $keepIds = [];

        foreach ($this->waters as $index => $waterData) {
            $water = null;

            if (! empty($waterData['id'])) {
                $water = $venue->waters()->whereKey($waterData['id'])->first();
            }

            $attributes = [
                'name' => $waterData['name'],
                'description' => $waterData['description'] ?: null,
                'peg_count' => $waterData['peg_count'] !== '' ? $waterData['peg_count'] : null,
                'depth_info' => $waterData['depth_info'] ?: null,
                'facilities' => Water::normalizeFacilities($waterData['facilities'] ?? []),
                'sort_order' => $index,
            ];

            if ($water) {
                $water->update($attributes);
            } else {
                $water = $venue->waters()->create($attributes);
            }

            $speciesIds = collect($waterData['species'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            $water->species()->sync($speciesIds);
            app(\App\Services\WaterPegService::class)->syncForWater(
                $water,
                $waterData['pegs'] ?? [],
                auth()->user(),
            );
            $keepIds[] = $water->id;
        }

        $venue->waters()->whereNotIn('id', $keepIds)->each(function (Water $water) {
            $water->species()->detach();
            $water->delete();
        });
    }

    private function syncPhotos(Venue $venue): void
    {
        $venue->photos()
            ->whereNotIn('id', $this->existingPhotoIds)
            ->get()
            ->each(function (VenuePhoto $photo) {
                Uploads::delete($photo->image_path);
                $photo->delete();
            });

        $sortOrder = count($this->existingPhotoIds);

        foreach ($this->newPhotos as $photo) {
            $path = Uploads::store($photo, 'venue-photos');
            $venue->photos()->create([
                'image_path' => $path,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.venue-wizard', [
            'speciesOptions' => Species::query()->orderBy('name')->get(),
            'steps' => [
                1 => 'Location',
                2 => 'Name',
                3 => 'Overview',
                4 => 'Address',
                5 => 'Details',
            ],
        ]);
    }

    private function setLocation(float $latitude, float $longitude, ?string $address, GeocodingService $geocoding): void
    {
        $this->latitude = round($latitude, 7);
        $this->longitude = round($longitude, 7);
        $this->locationSet = true;

        if (filled($address)) {
            $this->address = $address;
        } else {
            $result = $geocoding->reverse($this->latitude, $this->longitude);
            if ($result['address'] !== '') {
                $this->address = $result['address'];
            }
        }

        $this->dispatch('venue-location-updated', lat: $this->latitude, lng: $this->longitude);
    }

    private function validateStep(int $step): void
    {
        match ($step) {
            1 => $this->validate([
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'locationSet' => ['accepted'],
            ], [
                'locationSet.accepted' => 'Choose a location on the map or from search results.',
            ]),
            2 => $this->validate([
                'name' => ['required', 'string', 'max:255'],
            ]),
            3 => $this->validate([
                'overview' => ['nullable', 'string'],
            ]),
            4 => $this->validate([
                'address' => ['nullable', 'string', 'max:255'],
                'url' => ['nullable', 'url', 'max:2048'],
                'facebookUrl' => ['nullable', 'url', 'max:2048'],
                'what3words' => ['nullable', 'string', 'max:64', 'regex:/^[a-zA-Z0-9]+\.[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/'],
                'directions' => ['nullable', 'string'],
                'newPhotos' => ['nullable', 'array', 'max:8'],
                'newPhotos.*' => ['image', 'max:5120'],
            ], [
                'what3words.regex' => 'Enter three words separated by dots, e.g. filled.count.soap',
            ]),
            5 => $this->validate(array_filter([
                'ticket_type' => ['required', 'in:day_ticket,club,syndicate,mixed'],
                'day_ticket_info' => ['nullable', 'string'],
                'membership_info' => ['nullable', 'string'],
                'opening_times' => ['nullable', 'string'],
                'season_info' => ['nullable', 'string'],
                'tactics_guide' => $this->editRequest ? null : ['nullable', 'string'],
                'waters' => ['required', 'array', 'min:1'],
                'waters.*.name' => ['required', 'string', 'max:255'],
                'waters.*.description' => ['nullable', 'string'],
                'waters.*.peg_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
                'waters.*.depth_info' => ['nullable', 'string'],
                'waters.*.species' => ['nullable', 'array'],
                'waters.*.species.*' => ['integer', 'exists:species,id'],
                'waters.*.facilities' => ['nullable', 'array'],
                'waters.*.facilities.*' => ['string', 'in:'.implode(',', array_keys(Water::FACILITIES))],
                'waters.*.pegs' => ['nullable', 'array'],
                'waters.*.pegs.*.name' => ['nullable', 'string', 'max:100'],
                'waters.*.pegs.*.number' => ['nullable', 'string', 'max:50'],
                'waters.*.pegs.*.latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:waters.*.pegs.*.longitude'],
                'waters.*.pegs.*.longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:waters.*.pegs.*.latitude'],
                'waters.*.pegs.*.new_photos' => ['nullable', 'array', 'max:4'],
                'waters.*.pegs.*.new_photos.*' => ['nullable', 'image', 'max:5120'],
                'waters.*.pegs.*.existing_photo_ids' => ['nullable', 'array'],
                'waters.*.pegs.*.existing_photo_ids.*' => ['integer'],
            ])),
            default => null,
        };
    }

    private function authorizeEdit(Venue $venue): void
    {
        if ($this->admin) {
            $this->authorize('update', $venue);

            return;
        }

        $this->authorize('manage', $venue);
    }

    private function resolveVenueModel(mixed $venue): ?Venue
    {
        if ($venue instanceof Venue) {
            return $venue->loadMissing(['waters.species', 'photos']);
        }

        if (is_numeric($venue)) {
            return Venue::query()->with(['waters.species', 'photos'])->find($venue);
        }

        return null;
    }

    private function fillFromVenue(Venue $venue): void
    {
        $this->latitude = (float) $venue->latitude;
        $this->longitude = (float) $venue->longitude;
        $this->locationSet = true;
        $this->name = $venue->name;
        $this->slugPreview = $venue->slug;
        $this->overview = (string) $venue->overview;
        $this->address = (string) $venue->address;
        $this->url = (string) $venue->url;
        $this->facebookUrl = (string) $venue->facebook_url;
        $this->what3words = (string) $venue->what3words;
        $this->directions = (string) $venue->directions;
        $this->ticket_type = $venue->ticket_type;
        $this->day_ticket_info = (string) $venue->day_ticket_info;
        $this->membership_info = (string) $venue->membership_info;
        $this->opening_times = (string) $venue->opening_times;
        $this->season_info = (string) $venue->season_info;
        $this->tactics_guide = (string) $venue->tactics_guide;
        $this->is_complex = (bool) $venue->is_complex;
        $this->is_approved = (bool) $venue->is_approved;
        $this->manager_verified = (bool) $venue->manager_verified;
        $this->existingPhotoIds = $venue->photos->pluck('id')->all();

        if ($venue->waters->isNotEmpty()) {
            $this->waters = $venue->waters->map(fn (Water $water) => [
                'id' => $water->id,
                'name' => $water->name,
                'description' => (string) $water->description,
                'peg_count' => $water->peg_count,
                'depth_info' => (string) $water->depth_info,
                'species' => $water->species->pluck('id')->map(fn ($id) => (string) $id)->all(),
                'facilities' => $water->facilities ?? [],
                'pegs' => $water->pegs()->verified()->with('photos')->orderBy('sort_order')->get()->map(fn ($peg) => [
                    'id' => $peg->id,
                    'name' => (string) $peg->name,
                    'number' => (string) $peg->number,
                    'latitude' => $peg->latitude,
                    'longitude' => $peg->longitude,
                    'existing_photo_ids' => $peg->photos->pluck('id')->all(),
                    'existing_photos' => $peg->photos->map(fn ($photo) => [
                        'id' => $photo->id,
                        'url' => $photo->url(),
                    ])->values()->all(),
                    'new_photos' => [],
                ])->values()->all(),
            ])->values()->all();
        }
    }
}
