@php
    $lat = $get('latitude');
    $lng = $get('longitude');
    $initialLat = is_numeric($lat) ? (float) $lat : 54.7767;
    $initialLng = is_numeric($lng) ? (float) $lng : -1.5753;
@endphp

<div
    wire:ignore
    x-data="{
        map: null,
        marker: null,
        lat: {{ $initialLat }},
        lng: {{ $initialLng }},
        init() {
            this.$nextTick(() => this.waitForLeaflet());
        },
        waitForLeaflet(attempt = 0) {
            if (typeof L !== 'undefined') {
                this.initMap();
                return;
            }
            if (attempt > 50) {
                console.error('Leaflet failed to load for peg map.');
                return;
            }
            setTimeout(() => this.waitForLeaflet(attempt + 1), 100);
        },
        initMap() {
            const el = this.$refs.map;
            if (! el || this.map) return;

            this.map = L.map(el, { scrollWheelZoom: false }).setView([this.lat, this.lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(this.map);

            this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => {
                const pos = this.marker.getLatLng();
                this.persist(pos.lat, pos.lng);
            });
            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.persist(e.latlng.lat, e.latlng.lng);
            });

            [50, 250, 600].forEach((ms) => setTimeout(() => this.map?.invalidateSize(), ms));
        },
        persist(lat, lng) {
            this.lat = Number(Number(lat).toFixed(7));
            this.lng = Number(Number(lng).toFixed(7));
            this.$wire.set('data.latitude', this.lat);
            this.$wire.set('data.longitude', this.lng);
        },
    }"
    class="fi-peg-location-map col-span-full"
>
    <div class="text-sm font-medium text-gray-950 dark:text-white mb-1">
        Location on map
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
        Click the map or drag the marker. Latitude and longitude update automatically.
    </p>
    <div
        x-ref="map"
        class="w-full overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-700"
        style="height: 18rem; min-height: 18rem;"
    ></div>
</div>

@assets
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        .fi-peg-location-map .leaflet-container,
        .fi-body .leaflet-container {
            z-index: 0;
            font: inherit;
        }

        /* Filament/Tailwind max-width on images breaks OSM tiles */
        .fi-peg-location-map .leaflet-container img,
        .fi-body .leaflet-container img,
        .venue-wizard .leaflet-container img {
            max-width: none !important;
            max-height: none !important;
        }
    </style>
@endassets
