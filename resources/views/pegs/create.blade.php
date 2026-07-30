<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Add peg · {{ $venue->name }}</h1>
        <p class="text-slate-600 mt-1">Official pegs are visible to all anglers when logging sessions.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="managerPegForm({
            lat: @js((float) $venue->latitude),
            lng: @js((float) $venue->longitude),
         })">
        <form method="POST"
              action="{{ route('pegs.store', $venue) }}"
              enctype="multipart/form-data"
              class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold mb-1">Water / lake</label>
                <select name="water_id" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Select water</option>
                    @foreach ($venue->waters as $water)
                        <option value="{{ $water->id }}" @selected(old('water_id') == $water->id)>{{ $water->name }}</option>
                    @endforeach
                </select>
                @error('water_id') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Peg number</label>
                    <input name="number" value="{{ old('number') }}" placeholder="e.g. 12" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    @error('number') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Peg name</label>
                    <input name="name" value="{{ old('name') }}" placeholder="e.g. Island, Car park end" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    @error('name') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Location on map</label>
                <p class="text-sm text-slate-600 mb-2">Click the map or drag the marker.</p>
                <div id="manager-peg-map"
                     class="w-full rounded-lg border-2 border-slate-400 overflow-hidden bg-slate-200"
                     style="height: 18rem; min-height: 18rem;"></div>
                <input type="hidden" name="latitude" :value="lat">
                <input type="hidden" name="longitude" :value="lng">
                <p class="text-xs text-slate-500 mt-2" x-text="lat.toFixed(5) + ', ' + lng.toFixed(5)"></p>
                @error('latitude') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                @error('longitude') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Peg photos (optional)</label>
                <input type="file" name="photos[]" accept="image/*" capture="environment" multiple class="block w-full text-sm">
                @error('photos.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button class="px-5 py-3 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">Save peg</button>
                <a href="{{ route('venues.show', $venue) }}" class="px-5 py-3 rounded-md border-2 border-slate-400 font-semibold">Cancel</a>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            function managerPegForm({ lat, lng }) {
                return {
                    lat: Number(lat),
                    lng: Number(lng),
                    map: null,
                    marker: null,
                    init() {
                        this.$nextTick(() => this.waitForLeaflet());
                    },
                    waitForLeaflet(attempt = 0) {
                        if (typeof L !== 'undefined') {
                            this.initMap();
                            return;
                        }
                        if (attempt > 50) return;
                        setTimeout(() => this.waitForLeaflet(attempt + 1), 100);
                    },
                    initMap() {
                        const el = document.getElementById('manager-peg-map');
                        if (!el || this.map) return;

                        this.map = L.map(el).setView([this.lat, this.lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);

                        this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
                        this.marker.on('dragend', () => {
                            const pos = this.marker.getLatLng();
                            this.lat = Number(pos.lat.toFixed(7));
                            this.lng = Number(pos.lng.toFixed(7));
                        });
                        this.map.on('click', (e) => {
                            this.lat = Number(e.latlng.lat.toFixed(7));
                            this.lng = Number(e.latlng.lng.toFixed(7));
                            this.marker.setLatLng(e.latlng);
                        });

                        setTimeout(() => this.map.invalidateSize(), 50);
                        setTimeout(() => this.map.invalidateSize(), 250);
                    }
                };
            }
        </script>
    </x-slot>
</x-app-layout>
