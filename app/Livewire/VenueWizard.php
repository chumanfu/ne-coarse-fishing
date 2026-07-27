<?php

namespace App\Livewire;

use App\Filament\Resources\Venues\VenueResource;
use App\Models\Species;
use App\Models\Venue;
use App\Models\Water;
use App\Services\GeocodingService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class VenueWizard extends Component
{
    use AuthorizesRequests;

    public ?int $venueId = null;

    public bool $admin = false;

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

    /** @var list<array{id: ?int, name: string, description: string, peg_count: mixed, depth_info: string, species: list<int|string>}> */
    public array $waters = [];

    public function mount(mixed $venue = null, bool $admin = false): void
    {
        $this->admin = $admin || auth()->user()?->hasRole('super_admin') === true;

        $this->waters = [[
            'id' => null,
            'name' => '',
            'description' => '',
            'peg_count' => '',
            'depth_info' => '',
            'species' => [],
        ]];

        $model = $this->resolveVenueModel($venue);

        if ($model) {
            $this->authorizeEdit($model);
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
        ];
        $this->is_complex = true;
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

    public function save(): mixed
    {
        foreach (range(1, 5) as $step) {
            $this->validateStep($step);
        }

        $isUpdate = filled($this->venueId);
        $existingVenue = $isUpdate ? Venue::query()->findOrFail($this->venueId) : null;

        if ($existingVenue) {
            $this->authorizeEdit($existingVenue);
        } else {
            $this->authorize('create', Venue::class);
        }

        $venue = DB::transaction(function () use ($existingVenue) {
            $payload = [
                'name' => $this->name,
                'overview' => $this->overview ?: null,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'address' => $this->address ?: null,
                'directions' => $this->directions ?: null,
                'day_ticket_info' => $this->day_ticket_info ?: null,
                'membership_info' => $this->membership_info ?: null,
                'ticket_type' => $this->ticket_type,
                'opening_times' => $this->opening_times ?: null,
                'season_info' => $this->season_info ?: null,
                'tactics_guide' => $this->tactics_guide ?: null,
                'is_complex' => $this->is_complex || count($this->waters) > 1,
            ];

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

            $keepIds = [];

            foreach ($this->waters as $index => $waterData) {
                $water = null;

                if (! empty($waterData['id']) && $venue) {
                    $water = $venue->waters()->whereKey($waterData['id'])->first();
                }

                $attributes = [
                    'name' => $waterData['name'],
                    'description' => $waterData['description'] ?: null,
                    'peg_count' => $waterData['peg_count'] !== '' ? $waterData['peg_count'] : null,
                    'depth_info' => $waterData['depth_info'] ?: null,
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
                $keepIds[] = $water->id;
            }

            $venue->waters()->whereNotIn('id', $keepIds)->each(function (Water $water) {
                $water->species()->detach();
                $water->delete();
            });

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
                'directions' => ['nullable', 'string'],
            ]),
            5 => $this->validate([
                'ticket_type' => ['required', 'in:day_ticket,club,syndicate,mixed'],
                'day_ticket_info' => ['nullable', 'string'],
                'membership_info' => ['nullable', 'string'],
                'opening_times' => ['nullable', 'string'],
                'season_info' => ['nullable', 'string'],
                'tactics_guide' => ['nullable', 'string'],
                'waters' => ['required', 'array', 'min:1'],
                'waters.*.name' => ['required', 'string', 'max:255'],
                'waters.*.description' => ['nullable', 'string'],
                'waters.*.peg_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
                'waters.*.depth_info' => ['nullable', 'string'],
                'waters.*.species' => ['nullable', 'array'],
                'waters.*.species.*' => ['integer', 'exists:species,id'],
            ]),
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
            return $venue->loadMissing('waters.species');
        }

        if (is_numeric($venue)) {
            return Venue::query()->with('waters.species')->find($venue);
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

        if ($venue->waters->isNotEmpty()) {
            $this->waters = $venue->waters->map(fn (Water $water) => [
                'id' => $water->id,
                'name' => $water->name,
                'description' => (string) $water->description,
                'peg_count' => $water->peg_count,
                'depth_info' => (string) $water->depth_info,
                'species' => $water->species->pluck('id')->map(fn ($id) => (string) $id)->all(),
            ])->values()->all();
        }
    }
}
