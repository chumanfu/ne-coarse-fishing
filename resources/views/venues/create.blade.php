@php
    $editing = isset($venue);
    $initialWaters = old('waters', $editing
        ? $venue->waters->map(fn ($w) => [
            'id' => $w->id,
            'name' => $w->name,
            'description' => $w->description,
            'peg_count' => $w->peg_count,
            'depth_info' => $w->depth_info,
            'species' => $w->species->pluck('id')->map(fn ($id) => (string) $id)->all(),
        ])->values()->all()
        : [['id' => null, 'name' => '', 'description' => '', 'peg_count' => '', 'depth_info' => '', 'species' => []]]
    );
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">{{ $editing ? 'Edit venue' : 'Submit a venue' }}</h1>
        <p class="text-slate-600 mt-1">{{ $editing ? 'Update waters, tickets and tactics.' : 'New submissions go into the moderation queue before appearing publicly.' }}</p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="venueForm({
            lat: {{ old('latitude', $venue->latitude ?? 54.7767) }},
            lng: {{ old('longitude', $venue->longitude ?? -1.5753) }},
            waters: @js($initialWaters),
            isComplex: {{ old('is_complex', $venue->is_complex ?? false) ? 'true' : 'false' }}
         })">
        <form method="POST" action="{{ $editing ? route('venues.update', $venue) : route('venues.store') }}" class="space-y-6">
            @csrf
            @if ($editing) @method('PUT') @endif

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
                <h2 class="text-lg font-bold">Basics</h2>
                <div>
                    <label class="block text-sm font-semibold mb-1">Venue name</label>
                    <input name="name" value="{{ old('name', $venue->name ?? '') }}" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    @error('name') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Overview</label>
                    <textarea name="overview" rows="4" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('overview', $venue->overview ?? '') }}</textarea>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Ticket type</label>
                        <select name="ticket_type" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            @foreach (['day_ticket' => 'Day Ticket', 'club' => 'Club', 'syndicate' => 'Syndicate', 'mixed' => 'Mixed'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('ticket_type', $venue->ticket_type ?? 'day_ticket') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 font-semibold text-sm">
                            <input type="checkbox" name="is_complex" value="1" x-model="isComplex" class="rounded border-2 border-slate-400 text-sky-700 focus:ring-sky-700">
                            This is a complex with multiple waters
                        </label>
                    </div>
                </div>
            </section>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
                <h2 class="text-lg font-bold">Location</h2>
                <p class="text-sm text-slate-600">Drop a pin on the map or type coordinates. Default centre is Durham.</p>
                <div id="picker-map" class="h-72 rounded-lg border-2 border-slate-400"></div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Latitude</label>
                        <input type="number" step="any" name="latitude" x-model="lat" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude" x-model="lng" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Address</label>
                    <input name="address" value="{{ old('address', $venue->address ?? '') }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Directions / parking</label>
                    <textarea name="directions" rows="3" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('directions', $venue->directions ?? '') }}</textarea>
                </div>
            </section>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
                <h2 class="text-lg font-bold">Tickets, membership &amp; rules</h2>
                <div>
                    <label class="block text-sm font-semibold mb-1">Day ticket info</label>
                    <textarea name="day_ticket_info" rows="3" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="Prices, where to buy…">{{ old('day_ticket_info', $venue->day_ticket_info ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Membership info</label>
                    <textarea name="membership_info" rows="3" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('membership_info', $venue->membership_info ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Opening times</label>
                    <textarea name="opening_times" rows="2" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('opening_times', $venue->opening_times ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Seasonal restrictions</label>
                    <textarea name="season_info" rows="2" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('season_info', $venue->season_info ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tactics &amp; local knowledge</label>
                    <textarea name="tactics_guide" rows="4" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('tactics_guide', $venue->tactics_guide ?? '') }}</textarea>
                </div>
            </section>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-bold">Waters / ponds</h2>
                    <button type="button" @click="addWater()" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Add water</button>
                </div>

                <template x-for="(water, index) in waters" :key="index">
                    <div class="border-2 border-slate-200 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold" x-text="'Water ' + (index + 1)"></h3>
                            <button type="button" @click="removeWater(index)" x-show="waters.length > 1" class="text-sm font-semibold text-red-700">Remove</button>
                        </div>
                        <input type="hidden" :name="`waters[${index}][id]`" :value="water.id || ''">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Name</label>
                            <input :name="`waters[${index}][name]`" x-model="water.name" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="e.g. Match Lake">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Description</label>
                            <textarea :name="`waters[${index}][description]`" x-model="water.description" rows="2" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700"></textarea>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Peg count</label>
                                <input type="number" :name="`waters[${index}][peg_count]`" x-model="water.peg_count" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Depth info</label>
                                <input :name="`waters[${index}][depth_info]`" x-model="water.depth_info" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="e.g. 4–8ft">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Species</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach ($species as $item)
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox"
                                               :name="`waters[${index}][species][]`"
                                               value="{{ $item->id }}"
                                               :checked="water.species.includes('{{ $item->id }}')"
                                               @change="toggleSpecies(index, '{{ $item->id }}', $event.target.checked)"
                                               class="rounded border-slate-400 text-sky-700 focus:ring-sky-700">
                                        {{ $item->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </template>
                @error('waters') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
            </section>

            <div class="flex gap-3">
                <button class="px-5 py-3 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">{{ $editing ? 'Save changes' : 'Submit for approval' }}</button>
                <a href="{{ $editing ? route('venues.show', $venue) : route('venues.index') }}" class="px-5 py-3 rounded-md border-2 border-slate-400 font-semibold">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function venueForm({ lat, lng, waters, isComplex }) {
                return {
                    lat,
                    lng,
                    waters,
                    isComplex,
                    map: null,
                    marker: null,
                    init() {
                        this.$nextTick(() => this.initMap());
                        this.$watch('lat', () => this.syncMarker());
                        this.$watch('lng', () => this.syncMarker());
                        this.$watch('waters', (value) => {
                            if (value.length > 1) this.isComplex = true;
                        });
                    },
                    initMap() {
                        this.map = L.map('picker-map').setView([this.lat, this.lng], 10);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);
                        this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
                        this.marker.on('dragend', () => {
                            const { lat, lng } = this.marker.getLatLng();
                            this.lat = Number(lat.toFixed(7));
                            this.lng = Number(lng.toFixed(7));
                        });
                        this.map.on('click', (e) => {
                            this.lat = Number(e.latlng.lat.toFixed(7));
                            this.lng = Number(e.latlng.lng.toFixed(7));
                            this.syncMarker();
                        });
                    },
                    syncMarker() {
                        if (!this.marker) return;
                        this.marker.setLatLng([this.lat, this.lng]);
                    },
                    addWater() {
                        this.waters.push({ id: null, name: '', description: '', peg_count: '', depth_info: '', species: [] });
                        this.isComplex = true;
                    },
                    removeWater(index) {
                        if (this.waters.length > 1) this.waters.splice(index, 1);
                    },
                    toggleSpecies(index, id, checked) {
                        const list = this.waters[index].species;
                        if (checked && !list.includes(id)) list.push(id);
                        if (!checked) this.waters[index].species = list.filter(s => s !== id);
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
