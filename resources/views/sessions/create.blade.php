<x-app-layout>
    @php
        $editing = filled($session ?? null);
        $selectedVenueId = old('venue_id', $editing ? $session->venue_id : ($venue->id ?? ''));
        $defaultCatches = old('catches', $editing && $session->catches->isNotEmpty()
            ? $session->catches->map(fn ($c) => [
                'species_id' => (string) $c->species_id,
                'weight_lb' => $c->weight_lb,
                'bait' => $c->bait,
                'quantity' => $c->quantity,
            ])->values()->all()
            : [['species_id' => '', 'weight_lb' => '', 'bait' => '', 'quantity' => 1]]);
        $tacticsTip = old('tactics_tip', $editing ? ($session->venueTactic?->body ?? $session->tactics_tip) : '');
        $initialPegLat = old('peg_latitude', $editing ? $session->peg_latitude : null);
        $initialPegLng = old('peg_longitude', $editing ? $session->peg_longitude : null);
        $initialWaterId = old(
            'water_id',
            $editing ? ($session->water_id ?? $session->waterPeg?->water_id ?? null) : null,
        );
        if ($initialWaterId === null || $initialWaterId === '') {
            $initialWaterId = 'all';
        } else {
            $initialWaterId = (string) $initialWaterId;
        }
    @endphp

    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">{{ $editing ? 'Edit fishing session' : 'Log a fishing session' }}</h1>
        <p class="text-slate-600 mt-1">Capture date, peg, weather, catches, photos and tactics tips for the venue guide.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="sessionForm({
            venueId: @js((string) $selectedVenueId),
            watersByVenue: @js($watersJson),
            venuesById: @js($venuesJson),
            pegsByVenue: @js($pegsJson),
            catches: @js($defaultCatches),
            pegMode: @js(old('peg_mode', $editing && $session->water_peg_id ? 'existing' : ($editing && $session->hasPegLocation() ? 'new' : 'existing'))),
            waterPegId: @js((string) old('water_peg_id', $editing ? ($session->water_peg_id ?? '') : '')),
            waterId: @js((string) $initialWaterId),
            pegLat: @js($initialPegLat !== null && $initialPegLat !== '' ? (float) $initialPegLat : null),
            pegLng: @js($initialPegLng !== null && $initialPegLng !== '' ? (float) $initialPegLng : null),
         })">
        <form method="POST"
              action="{{ $editing ? route('sessions.update', $session) : route('sessions.store') }}"
              enctype="multipart/form-data"
              class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf
            @if ($editing)
                @method('PATCH')
            @endif

            <div>
                <label class="block text-sm font-semibold mb-1">Venue</label>
                <select name="venue_id" x-model="venueId" @change="onVenueChange()" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Select venue</option>
                    @foreach ($venues as $item)
                        <option value="{{ $item->id }}" @selected($selectedVenueId == $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
                @error('venue_id') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Water / lake</label>
                <select name="water_id" x-model="waterId" @change="onWaterChange()" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="all">Whole venue / not sure</option>
                    <template x-for="water in currentWaters" :key="'water-' + water.id">
                        <option :value="String(water.id)" :selected="String(waterId) === String(water.id)" x-text="water.name"></option>
                    </template>
                </select>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Date fished</label>
                    <input type="date" name="fished_at" value="{{ old('fished_at', $editing ? $session->fished_at->toDateString() : now()->toDateString()) }}" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Duration (hours)</label>
                    <input type="number" name="duration_hours" value="{{ old('duration_hours', $editing ? $session->duration_hours : '') }}" min="1" max="72" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Weather</label>
                    <input name="weather" value="{{ old('weather', $editing ? $session->weather : '') }}" placeholder="Overcast, light SW wind" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
            </div>

            <div class="space-y-3 border-2 border-slate-200 rounded-xl p-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Peg</label>
                    <p class="text-sm text-slate-600 mb-2">Choose an official peg, or add a new one (pending verification if you don’t manage the venue).</p>
                    <div class="flex flex-wrap gap-3 text-sm font-semibold">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="peg_mode" value="existing" x-model="pegMode"> Existing peg
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="peg_mode" value="new" x-model="pegMode" @change="ensureMap()"> Add new peg
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="peg_mode" value="none" x-model="pegMode"> No peg
                        </label>
                    </div>
                </div>

                <div x-show="pegMode === 'existing'" x-cloak>
                    <select name="water_peg_id" x-model="waterPegId" @change="selectExistingPeg()" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                        <option value="">Select peg</option>
                        <template x-for="peg in currentPegs" :key="peg.id">
                            <option :value="String(peg.id)" x-text="peg.label + (peg.verified ? '' : ' (your pending peg)')"></option>
                        </template>
                    </select>
                    <p class="text-xs text-slate-500 mt-2" x-show="!venueId">Select a venue first to see its pegs.</p>
                    <p class="text-xs text-slate-500 mt-2" x-show="venueId && isWholeVenue && currentPegs.length > 0">Showing pegs from every water at this venue. Pick a specific water to narrow the list.</p>
                    <p class="text-xs text-slate-500 mt-2" x-show="venueId && currentPegs.length === 0">No pegs listed yet — add a new one (choose a specific water first).</p>
                </div>

                <div x-show="pegMode === 'new'" x-cloak class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Peg number</label>
                        <input name="peg_number" value="{{ old('peg_number', $editing && ! $session->water_peg_id ? $session->peg_number : '') }}" placeholder="e.g. 12" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Peg name</label>
                        <input name="peg_name" value="{{ old('peg_name') }}" placeholder="e.g. Island, Car park end" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                </div>

                <div x-show="pegMode === 'new' && isWholeVenue" x-cloak class="mt-1">
                    <p class="text-sm text-amber-800 font-semibold">Choose a specific water/lake above before adding a new peg.</p>
                </div>

                <div x-show="pegMode === 'new' && !isWholeVenue" x-cloak class="mt-3">
                    <label class="block text-sm font-semibold mb-1">Peg photos</label>
                    <p class="text-sm text-slate-600 mb-2">Optional. Photos of a new peg stay pending until the venue owner verifies the peg.</p>
                    <input type="file" name="peg_photos[]" accept="image/*" capture="environment" multiple class="block w-full text-sm">
                    @error('peg_photos') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                    @error('peg_photos.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-show="(pegMode === 'new' && !isWholeVenue) || (pegMode === 'existing' && waterPegId)" x-cloak>
                    <div class="flex flex-wrap items-end justify-between gap-3 mb-2">
                        <div>
                            <label class="block text-sm font-semibold mb-1" x-text="pegMode === 'new' ? 'Mark peg on map' : 'Peg location'"></label>
                            <p class="text-sm text-slate-600" x-show="pegMode === 'new'">Click the map or drag the marker.</p>
                        </div>
                        <button type="button"
                                @click="clearPegLocation()"
                                x-show="pegMode === 'new' && pegLat !== null && pegLng !== null"
                                class="text-sm font-semibold text-slate-700 hover:text-sky-800">
                            Clear pin
                        </button>
                    </div>
                    <div id="session-peg-map"
                         class="w-full rounded-lg border-2 border-slate-400 overflow-hidden bg-slate-200"
                         style="height: 18rem; min-height: 18rem;"></div>
                    <input type="hidden" name="peg_latitude" :value="pegLat ?? ''">
                    <input type="hidden" name="peg_longitude" :value="pegLng ?? ''">
                    <p class="text-xs text-slate-500 mt-2" x-show="pegLat !== null && pegLng !== null" x-cloak>
                        Pin: <span x-text="pegLat?.toFixed(5)"></span>, <span x-text="pegLng?.toFixed(5)"></span>
                    </p>
                    @error('peg_latitude') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                    @error('peg_longitude') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Commentary / write-up</label>
                <textarea name="commentary" rows="5" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('commentary', $editing ? $session->commentary : '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Tactics tip for the venue guide</label>
                <p class="text-sm text-slate-600 mb-2">Share what worked — baits, pegs, conditions. This appears in the venue’s tactics section for other anglers.</p>
                <textarea name="tactics_tip" rows="4" placeholder="e.g. Margin pole with maggot and caster on peg 12 in a warm south-westerly." class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ $tacticsTip }}</textarea>
                @error('tactics_tip') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold">Fish caught</label>
                    <button type="button" @click="addCatch()" class="text-sm font-semibold text-sky-800">Add catch</button>
                </div>
                <template x-for="(catchItem, index) in catches" :key="index">
                    <div class="grid sm:grid-cols-4 gap-2 mb-2">
                        <select :name="`catches[${index}][species_id]`" x-model="catchItem.species_id" class="rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            <option value="">Species</option>
                            @foreach ($species as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" :name="`catches[${index}][weight_lb]`" x-model="catchItem.weight_lb" placeholder="Weight (lb)" class="rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                        <input :name="`catches[${index}][bait]`" x-model="catchItem.bait" placeholder="Bait" class="rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                        <div class="flex gap-2">
                            <input type="number" :name="`catches[${index}][quantity]`" x-model="catchItem.quantity" min="1" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            <button type="button" @click="removeCatch(index)" class="text-red-700 font-semibold text-sm">×</button>
                        </div>
                    </div>
                </template>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Photos (up to 6, mobile friendly)</label>
                @if ($editing && $session->photos->isNotEmpty())
                    <div class="mb-3"
                         x-data="{
                            removed: @js(collect(old('remove_photo_ids', []))->map(fn ($id) => (int) $id)->values()->all()),
                            toggle(id) {
                                if (this.removed.includes(id)) {
                                    this.removed = this.removed.filter((item) => item !== id);
                                } else {
                                    this.removed.push(id);
                                }
                            },
                         }">
                        <p class="text-sm text-slate-600 mb-2">Mark any to remove, then save. You can also add more below.</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($session->photos as $photo)
                                <figure class="relative rounded-lg border-2 overflow-hidden bg-slate-100"
                                        :class="removed.includes({{ $photo->id }}) ? 'border-red-400 opacity-50' : 'border-slate-300'">
                                    <img src="{{ $photo->url() }}" alt="Session photo" class="object-cover h-28 w-full">
                                    <button type="button"
                                            @click="toggle({{ $photo->id }})"
                                            class="absolute inset-x-0 bottom-0 text-white text-sm font-semibold py-1.5"
                                            :class="removed.includes({{ $photo->id }}) ? 'bg-sky-800 hover:bg-sky-900' : 'bg-slate-900/75 hover:bg-red-800'"
                                            x-text="removed.includes({{ $photo->id }}) ? 'Keep' : 'Remove'">
                                    </button>
                                </figure>
                            @endforeach
                        </div>
                        <template x-for="id in removed" :key="'remove-photo-' + id">
                            <input type="hidden" name="remove_photo_ids[]" :value="id">
                        </template>
                    </div>
                @endif
                <input type="file" name="photos[]" accept="image/*" capture="environment" multiple class="block w-full text-sm">
                @error('photos.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                @error('remove_photo_ids') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                @error('remove_photo_ids.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button class="px-5 py-3 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">{{ $editing ? 'Save changes' : 'Save session' }}</button>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            function sessionForm({ venueId, watersByVenue, venuesById, pegsByVenue, catches, pegMode, waterPegId, waterId, pegLat, pegLng }) {
                return {
                    venueId: venueId || '',
                    waterId: waterId || 'all',
                    watersByVenue: watersByVenue || {},
                    venuesById: venuesById || {},
                    pegsByVenue: pegsByVenue || {},
                    catches,
                    pegMode: pegMode || 'existing',
                    waterPegId: waterPegId || '',
                    pegLat,
                    pegLng,
                    map: null,
                    marker: null,
                    defaultCenter: [54.7767, -1.5753],
                    get isWholeVenue() {
                        return ! this.waterId || this.waterId === 'all';
                    },
                    get currentWaters() {
                        return this.watersByVenue[this.venueId] || this.watersByVenue[String(this.venueId)] || [];
                    },
                    get currentPegs() {
                        const byWater = this.pegsByVenue[this.venueId] || this.pegsByVenue[String(this.venueId)] || {};

                        if (this.isWholeVenue) {
                            return Object.entries(byWater).flatMap(([waterKey, pegs]) => {
                                const water = this.currentWaters.find(item => String(item.id) === String(waterKey));
                                const waterName = water?.name;

                                return (pegs || []).map((peg) => ({
                                    ...peg,
                                    label: waterName ? `${peg.label} · ${waterName}` : peg.label,
                                }));
                            });
                        }

                        return byWater[this.waterId] || byWater[String(this.waterId)] || [];
                    },
                    init() {
                        const desiredWaterId = this.waterId ? String(this.waterId) : 'all';
                        const desiredPegId = this.waterPegId ? String(this.waterPegId) : '';

                        if (! desiredWaterId || desiredWaterId === 'all') {
                            const inferred = this.waterIdForPeg(desiredPegId);
                            this.waterId = inferred || 'all';
                        } else {
                            this.waterId = desiredWaterId;
                        }

                        // Alpine x-for options mount after init; re-apply so the select shows the saved water.
                        this.$nextTick(() => {
                            const waterId = this.waterId;
                            this.waterId = 'all';
                            this.$nextTick(() => {
                                this.waterId = waterId;
                                if (desiredPegId) {
                                    this.waterPegId = desiredPegId;
                                    this.selectExistingPeg();
                                }
                            });
                        });

                        this.$watch('pegMode', () => this.$nextTick(() => this.ensureMap()));
                        this.$nextTick(() => this.ensureMap());
                    },
                    waterIdForPeg(pegId) {
                        if (! pegId || ! this.venueId) {
                            return null;
                        }

                        const byWater = this.pegsByVenue[this.venueId] || this.pegsByVenue[String(this.venueId)] || {};

                        for (const [waterKey, pegs] of Object.entries(byWater)) {
                            if ((pegs || []).some((peg) => String(peg.id) === String(pegId))) {
                                return String(waterKey);
                            }
                        }

                        return null;
                    },
                    ensureMap() {
                        if (this.pegMode === 'none') return;
                        if (this.pegMode === 'existing' && !this.waterPegId) return;
                        if (this.pegMode === 'new' && this.isWholeVenue) return;
                        this.waitForLeaflet();
                    },
                    waitForLeaflet(attempt = 0) {
                        if (typeof L !== 'undefined') {
                            this.initMap();
                            return;
                        }
                        if (attempt > 50) {
                            console.error('Leaflet failed to load for session peg map.');
                            return;
                        }
                        setTimeout(() => this.waitForLeaflet(attempt + 1), 100);
                    },
                    initMap() {
                        const el = document.getElementById('session-peg-map');
                        if (!el) return;

                        if (this.map) {
                            setTimeout(() => this.map.invalidateSize(), 50);
                            return;
                        }

                        const center = this.currentCenter();
                        const zoom = this.pegLat !== null && this.pegLng !== null ? 16 : (this.venueId ? 14 : 9);

                        this.map = L.map(el).setView(center, zoom);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);

                        this.marker = L.marker(center, { draggable: this.pegMode === 'new' }).addTo(this.map);
                        this.marker.on('dragend', () => {
                            if (this.pegMode !== 'new') return;
                            const pos = this.marker.getLatLng();
                            this.setPeg(pos.lat, pos.lng);
                        });
                        this.map.on('click', (e) => {
                            if (this.pegMode !== 'new') return;
                            this.setPeg(e.latlng.lat, e.latlng.lng);
                            this.marker.setLatLng(e.latlng);
                        });

                        if (this.pegMode === 'new' && (this.pegLat === null || this.pegLng === null)) {
                            this.marker.setOpacity(0.55);
                        }

                        setTimeout(() => this.map.invalidateSize(), 50);
                        setTimeout(() => this.map.invalidateSize(), 250);
                        setTimeout(() => this.map.invalidateSize(), 600);
                    },
                    currentCenter() {
                        if (this.pegLat !== null && this.pegLng !== null) {
                            return [this.pegLat, this.pegLng];
                        }
                        const venue = this.venuesById[this.venueId] || this.venuesById[String(this.venueId)];
                        if (venue && venue.lat != null && venue.lng != null) {
                            return [Number(venue.lat), Number(venue.lng)];
                        }
                        return this.defaultCenter;
                    },
                    setPeg(lat, lng) {
                        this.pegLat = Number(Number(lat).toFixed(7));
                        this.pegLng = Number(Number(lng).toFixed(7));
                        if (this.marker) this.marker.setOpacity(1);
                    },
                    clearPegLocation() {
                        this.pegLat = null;
                        this.pegLng = null;
                        if (!this.map || !this.marker) return;
                        const center = this.currentCenter();
                        this.marker.setLatLng(center);
                        this.marker.setOpacity(0.55);
                        this.map.setView(center, this.venueId ? 14 : 9);
                    },
                    selectExistingPeg() {
                        const peg = this.currentPegs.find(p => String(p.id) === String(this.waterPegId));
                        if (!peg) return;

                        if (this.isWholeVenue && peg.water_id) {
                            this.waterId = String(peg.water_id);
                        }

                        this.pegLat = peg.lat;
                        this.pegLng = peg.lng;
                        this.$nextTick(() => {
                            this.ensureMap();
                            if (this.marker) {
                                this.marker.setLatLng([peg.lat, peg.lng]);
                                this.marker.dragging?.disable();
                                this.marker.setOpacity(1);
                            }
                            if (this.map) this.map.setView([peg.lat, peg.lng], 16);
                        });
                    },
                    onVenueChange() {
                        this.waterId = 'all';
                        this.waterPegId = '';
                        this.pegLat = null;
                        this.pegLng = null;
                        if (this.map && this.marker) {
                            const center = this.currentCenter();
                            this.marker.setLatLng(center);
                            this.map.setView(center, 14);
                        }
                    },
                    onWaterChange() {
                        this.waterPegId = '';
                        if (this.pegMode === 'existing') {
                            this.pegLat = null;
                            this.pegLng = null;
                        }
                        this.$nextTick(() => this.ensureMap());
                    },
                    addCatch() {
                        this.catches.push({ species_id: '', weight_lb: '', bait: '', quantity: 1 });
                    },
                    removeCatch(index) {
                        this.catches.splice(index, 1);
                        if (!this.catches.length) this.addCatch();
                    }
                }
            }
        </script>
    </x-slot>
</x-app-layout>
