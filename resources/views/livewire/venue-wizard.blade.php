<div class="space-y-6">
    <ol class="grid grid-cols-2 sm:grid-cols-5 gap-2">
        @foreach ($steps as $number => $label)
            <li>
                <button type="button"
                        wire:click="goToStep({{ $number }})"
                        @disabled($number > $step)
                        class="w-full rounded-lg border-2 px-3 py-2 text-left text-sm font-semibold transition
                            {{ $step === $number ? 'border-sky-700 bg-sky-50 text-sky-950' : ($number < $step ? 'border-emerald-600 bg-emerald-50 text-emerald-950' : 'border-slate-300 bg-white text-slate-500') }}">
                    <span class="block text-xs uppercase tracking-wide opacity-70">Step {{ $number }}</span>
                    {{ $label }}
                </button>
            </li>
        @endforeach
    </ol>

    <div class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
        @if ($step === 1)
            <div wire:key="step-location"
                 x-data="venueWizardMap(@js($latitude), @js($longitude), @js($locationSet))"
                 x-on:venue-location-updated.window="moveMarker($event.detail.lat, $event.detail.lng)">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Find the water on the map</h2>
                    <p class="text-sm text-slate-600 mt-1">Search by UK postcode, place name, or paste latitude,longitude. Then drop or drag the pin.</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 mt-4">
                    <input type="text"
                           wire:model="searchQuery"
                           wire:keydown.enter.prevent="searchLocation"
                           placeholder="e.g. DH7 7AR or 54.79, -1.62"
                           class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <button type="button" wire:click="searchLocation" class="px-4 py-2 rounded-md bg-slate-900 text-white font-semibold whitespace-nowrap">
                        Search
                    </button>
                </div>
                @error('searchQuery') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
                @if ($searchError)
                    <p class="text-amber-800 text-sm font-medium">{{ $searchError }}</p>
                @endif

                @if (count($searchResults))
                    <ul class="divide-y divide-slate-200 border-2 border-slate-200 rounded-lg overflow-hidden mt-3">
                        @foreach ($searchResults as $index => $result)
                            <li>
                                <button type="button"
                                        wire:click="selectSearchResult({{ $index }})"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-sky-50">
                                    {{ $result['display_name'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div id="venue-wizard-map" class="h-80 rounded-lg border-2 border-slate-400 overflow-hidden bg-slate-200 mt-4" wire:ignore></div>

                <div class="grid sm:grid-cols-2 gap-3 text-sm mt-4">
                    <div>
                        <label class="block font-semibold mb-1">Latitude</label>
                        <input type="number" step="any" wire:model.blur="latitude" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Longitude</label>
                        <input type="number" step="any" wire:model.blur="longitude" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                </div>
                @error('locationSet') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
                @error('latitude') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
                @error('longitude') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror

                @if ($locationSet)
                    <p class="text-sm font-semibold text-emerald-800 mt-2">Location selected. You can refine the pin before continuing.</p>
                @endif
            </div>
        @endif

        @if ($step === 2)
            <div wire:key="step-name">
                <h2 class="text-lg font-bold text-slate-900">Name the venue</h2>
                <p class="text-sm text-slate-600 mt-1">The URL slug is generated automatically from the name.</p>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1">Venue name</label>
                    <input type="text" wire:model.live.debounce.300ms="name" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="e.g. Aldin Grange Lakes">
                    @error('name') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1">Slug</label>
                    <input type="text" value="{{ $slugPreview }}" readonly class="w-full rounded-md border-2 border-slate-300 bg-slate-50 text-slate-600">
                </div>
            </div>
        @endif

        @if ($step === 3)
            <div wire:key="step-overview">
                <h2 class="text-lg font-bold text-slate-900">Overview</h2>
                <p class="text-sm text-slate-600 mt-1">A short description of the fishery for anglers browsing the directory.</p>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1">Description / overview</label>
                    <textarea wire:model="overview" rows="8" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="What makes this water worth a visit?"></textarea>
                    @error('overview') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        @if ($step === 4)
            <div wire:key="step-address">
                <h2 class="text-lg font-bold text-slate-900">Address &amp; directions</h2>
                <p class="text-sm text-slate-600 mt-1">Address is pre-filled from the map pin — edit if needed.</p>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1">Address</label>
                    <input type="text" wire:model="address" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    @error('address') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1">Directions / parking</label>
                    <textarea wire:model="directions" rows="5" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="How to find the car park, access tracks, gate codes if appropriate…"></textarea>
                    @error('directions') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="button" wire:click="reverseGeocode" class="mt-3 text-sm font-semibold text-sky-800 hover:underline">
                    Refresh address from map pin
                </button>
            </div>
        @endif

        @if ($step === 5)
            <div wire:key="step-details" class="space-y-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Tickets, waters &amp; local knowledge</h2>
                    <p class="text-sm text-slate-600 mt-1">Everything else anglers need before they visit.</p>
                </div>

                @if ($admin)
                    <div class="rounded-lg border-2 border-amber-500 bg-amber-50 p-4 space-y-3">
                        <p class="text-sm font-bold text-amber-950">Admin options</p>
                        <label class="flex items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" wire:model="is_approved" class="rounded border-slate-400 text-sky-700 focus:ring-sky-700">
                            Approved for public listing
                        </label>
                        <label class="flex items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" wire:model="manager_verified" class="rounded border-slate-400 text-sky-700 focus:ring-sky-700">
                            Manager verified badge
                        </label>
                    </div>
                @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Ticket type</label>
                        <select wire:model="ticket_type" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            <option value="day_ticket">Day Ticket</option>
                            <option value="club">Club</option>
                            <option value="syndicate">Syndicate</option>
                            <option value="mixed">Mixed</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" wire:model="is_complex" class="rounded border-slate-400 text-sky-700 focus:ring-sky-700">
                            Complex with multiple waters
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Day ticket info</label>
                    <textarea wire:model="day_ticket_info" rows="3" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Membership info</label>
                    <textarea wire:model="membership_info" rows="3" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Opening times</label>
                    <textarea wire:model="opening_times" rows="2" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Seasonal restrictions</label>
                    <textarea wire:model="season_info" rows="2" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tactics &amp; local knowledge</label>
                    <textarea wire:model="tactics_guide" rows="4" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700"></textarea>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-bold">Waters / ponds</h3>
                        <button type="button" wire:click="addWater" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Add water</button>
                    </div>

                    @foreach ($waters as $index => $water)
                        <div class="border-2 border-slate-200 rounded-lg p-4 space-y-3" wire:key="water-{{ $index }}">
                            <div class="flex justify-between items-center">
                                <h4 class="font-semibold">Water {{ $index + 1 }}</h4>
                                @if (count($waters) > 1)
                                    <button type="button" wire:click="removeWater({{ $index }})" class="text-sm font-semibold text-red-700">Remove</button>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Name</label>
                                <input type="text" wire:model="waters.{{ $index }}.name" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                @error('waters.'.$index.'.name') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Description</label>
                                <textarea wire:model="waters.{{ $index }}.description" rows="2" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700"></textarea>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold mb-1">Peg count</label>
                                    <input type="number" wire:model="waters.{{ $index }}.peg_count" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1">Depth info</label>
                                    <input type="text" wire:model="waters.{{ $index }}.depth_info" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Species</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach ($speciesOptions as $species)
                                        <label class="inline-flex items-center gap-2 text-sm">
                                            <input type="checkbox"
                                                   wire:model="waters.{{ $index }}.species"
                                                   value="{{ $species->id }}"
                                                   class="rounded border-slate-400 text-sky-700 focus:ring-sky-700">
                                            {{ $species->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @error('waters') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-3 justify-between">
        <button type="button"
                wire:click="previousStep"
                @disabled($step === 1)
                class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold disabled:opacity-40">
            Back
        </button>

        <div class="flex gap-3">
            @if ($step < 5)
                <button type="button" wire:click="nextStep" class="px-5 py-2 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">
                    Continue
                </button>
            @else
                <button type="button" wire:click="save" class="px-5 py-2 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">
                    {{ $venue ? 'Save changes' : ($admin ? 'Create venue' : 'Submit for approval') }}
                </button>
            @endif
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('venueWizardMap', (lat, lng, locationSet) => ({
        map: null,
        marker: null,
        init() {
            this.$nextTick(() => this.initMap(lat, lng, locationSet));
        },
        initMap(lat, lng, locationSet) {
            const el = document.getElementById('venue-wizard-map');
            if (!el || typeof L === 'undefined') return;

            this.map = L.map(el).setView([lat || 54.7767, lng || -1.5753], locationSet ? 14 : 9);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            this.marker = L.marker([lat || 54.7767, lng || -1.5753], { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => {
                const pos = this.marker.getLatLng();
                this.$wire.setPin(Number(pos.lat.toFixed(7)), Number(pos.lng.toFixed(7)));
            });
            this.map.on('click', (e) => {
                this.$wire.setPin(Number(e.latlng.lat.toFixed(7)), Number(e.latlng.lng.toFixed(7)));
            });

            setTimeout(() => this.map.invalidateSize(), 80);
        },
        moveMarker(lat, lng) {
            if (!this.marker || !this.map) return;
            this.marker.setLatLng([lat, lng]);
            this.map.setView([lat, lng], Math.max(this.map.getZoom(), 14));
        }
    }));
</script>
@endscript
