<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Interactive map</h1>
                <p class="text-slate-600 mt-1">Venues and North East tackle shops — filter waters by species and ticket type.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
         x-data="venueMap(@js($markers), @js($filters))">
        <form method="GET" class="bg-white border-2 border-slate-300 rounded-xl p-4 mb-4 grid gap-3 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-semibold mb-1">Species</label>
                <select name="species" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Any species</option>
                    @foreach ($species as $item)
                        <option value="{{ $item->slug }}" @selected(($filters['species'] ?? '') === $item->slug)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Ticket type</label>
                <select name="ticket_type" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Any ticket</option>
                    <option value="day_ticket" @selected(($filters['ticket_type'] ?? '') === 'day_ticket')>Day Ticket</option>
                    <option value="club" @selected(($filters['ticket_type'] ?? '') === 'club')>Club</option>
                    <option value="syndicate" @selected(($filters['ticket_type'] ?? '') === 'syndicate')>Syndicate</option>
                    <option value="mixed" @selected(($filters['ticket_type'] ?? '') === 'mixed')>Mixed</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="px-4 py-2 rounded-md bg-slate-900 text-white font-semibold">Apply filters</button>
                <a href="{{ route('map.index') }}" class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold">Reset</a>
            </div>
        </form>

        <div class="flex flex-wrap items-center gap-4 mb-4 text-sm font-semibold text-slate-700">
            <span class="inline-flex items-center gap-2">
                <span class="map-legend-pin map-legend-pin--venue" aria-hidden="true"></span>
                Venues
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="map-legend-pin map-legend-pin--shop" aria-hidden="true"></span>
                Tackle shops
            </span>
        </div>

        <div class="grid lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2">
                <div id="region-map" class="h-[70vh] min-h-[420px] rounded-xl border-2 border-slate-500 overflow-hidden bg-slate-200"></div>
            </div>
            <aside class="bg-white border-2 border-slate-300 rounded-xl p-4 h-[70vh] min-h-[420px] overflow-y-auto">
                <template x-if="selected">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="text-xl font-bold text-slate-900" x-text="selected.name"></h2>
                            <span x-show="selected.type === 'venue' && selected.verified" class="text-xs font-bold uppercase bg-emerald-100 border border-emerald-600 text-emerald-900 px-2 py-1 rounded">Verified</span>
                            <span x-show="selected.type === 'tackle_shop'" class="text-xs font-bold uppercase bg-red-50 border border-red-500 text-red-900 px-2 py-1 rounded">Shop</span>
                        </div>
                        <p class="text-sm text-slate-600 mt-1" x-text="selected.address || 'Address not listed'"></p>
                        <p class="text-sm font-semibold mt-3" x-text="selected.ticket_type"></p>
                        <p class="text-sm text-slate-800 mt-3" x-text="selected.overview || 'No summary yet.'"></p>
                        <div class="mt-3 flex flex-wrap gap-2" x-show="selected.type === 'venue'">
                            <template x-for="name in selected.species" :key="name">
                                <span class="text-xs font-semibold bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded" x-text="name"></span>
                            </template>
                        </div>
                        <a :href="selected.url" class="mt-5 inline-flex px-4 py-2 rounded-md bg-sky-700 text-white font-semibold"
                           x-text="selected.type === 'tackle_shop' ? 'Open shop page' : 'Open venue page'"></a>
                    </div>
                </template>
                <template x-if="!selected">
                    <div class="text-slate-600 text-sm">
                        <p class="font-semibold text-slate-900 mb-2">
                            {{ $venueCount }} venues
                            @if ($shopCount > 0)
                                · {{ $shopCount }} tackle shops
                            @endif
                            plotted
                        </p>
                        <p>Click a pin to preview. Blue pins are venues; red pins are tackle shops. Venue filters hide shops until you reset.</p>
                    </div>
                </template>
            </aside>
        </div>
    </div>

    @push('styles')
        <style>
            .map-legend-pin {
                display: inline-block;
                width: 0.85rem;
                height: 0.85rem;
                border-radius: 999px;
                border: 2px solid #fff;
                box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.25);
            }
            .map-legend-pin--venue { background: #2563eb; }
            .map-legend-pin--shop { background: #dc2626; }
            .leaflet-div-icon.map-pin {
                background: transparent;
                border: none;
            }
            .map-pin__dot {
                display: block;
                width: 18px;
                height: 18px;
                border-radius: 999px;
                border: 2px solid #fff;
                box-shadow: 0 1px 4px rgba(15, 23, 42, 0.45);
            }
            .map-pin--venue .map-pin__dot { background: #2563eb; }
            .map-pin--shop .map-pin__dot { background: #dc2626; }
        </style>
    @endpush

    @push('scripts')
        <script>
            function venueMap(markers) {
                const pinIcon = (type) => L.divIcon({
                    className: `leaflet-div-icon map-pin map-pin--${type === 'tackle_shop' ? 'shop' : 'venue'}`,
                    html: '<span class="map-pin__dot"></span>',
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                    popupAnchor: [0, -10],
                });

                return {
                    markers,
                    selected: null,
                    map: null,
                    init() {
                        this.map = L.map('region-map').setView([54.9, -1.6], 9);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);

                        const bounds = [];
                        this.markers.forEach((marker) => {
                            if (marker.lat == null || marker.lng == null) {
                                return;
                            }

                            const pin = L.marker([marker.lat, marker.lng], {
                                icon: pinIcon(marker.type),
                            }).addTo(this.map);
                            const kind = marker.type === 'tackle_shop' ? 'Tackle shop' : 'Venue';
                            pin.bindPopup(`<strong>${marker.name}</strong><br>${kind} · ${marker.ticket_type}`);
                            pin.on('click', () => { this.selected = marker; });
                            bounds.push([marker.lat, marker.lng]);
                        });

                        if (bounds.length) {
                            this.map.fitBounds(bounds, { padding: [30, 30] });
                        }
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
