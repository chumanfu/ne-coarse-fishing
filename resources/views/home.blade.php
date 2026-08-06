<x-app-layout>
    <section class="home-hero" aria-label="Welcome">
        <div class="home-hero__media" aria-hidden="true">
            <img
                src="{{ asset('images/home/hero-tyne.jpg') }}"
                alt=""
                class="home-hero__image"
                width="1574"
                height="686"
                fetchpriority="high"
            >
            <div class="home-hero__shade"></div>
            <div class="home-hero__sparkle"></div>
        </div>

        <div class="home-hero__content">
            <p class="home-hero__eyebrow">North East · Coarse Angling</p>
            <h1 class="home-hero__title">
                Find the best waters from the Tyne to the Tees
            </h1>
            <p class="home-hero__lead">
                Discover day-ticket lakes, club complexes and canal stretches. Read local tactics, log your sessions, and follow official match reports.
            </p>

            <div class="home-hero__actions">
                <a href="{{ route('map.index') }}" class="home-hero__btn home-hero__btn--primary">Open the map</a>
                <a href="{{ route('venues.index') }}" class="home-hero__btn home-hero__btn--ghost">Browse venues</a>
                <a href="{{ route('clubs.index') }}" class="home-hero__btn home-hero__btn--ghost">Clubs</a>
                <a href="{{ route('tackle-shops.index') }}" class="home-hero__btn home-hero__btn--ghost">Tackle shops</a>
            </div>

            <p class="home-hero__stat">
                <span class="home-hero__stat-icon" aria-hidden="true"></span>
                <em>{{ $venueCount }} approved venues on the portal</em>
            </p>
        </div>

        <div class="home-hero__status">
            <span class="home-hero__status-icon" aria-hidden="true"></span>
            <p>Live map status: <strong>{{ count($mapMarkers) }} places ready</strong></p>
        </div>
    </section>

    <div class="border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Latest activity</h2>
                    <p class="text-slate-600 mt-1">New venues, sessions, tactics, clubs and tackle shops from around the region.</p>
                </div>
                <a href="{{ route('activity.index') }}" class="text-sky-800 font-semibold hover:underline shrink-0">View all</a>
            </div>

            <div class="space-y-3">
                @forelse ($activities as $activity)
                    <x-activity-row :activity="$activity" />
                @empty
                    <p class="text-slate-600">No activity yet — log a session or add a venue to get things started.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="border-t-2 border-slate-200 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12"
             id="home-map"
             x-data="homeVenueMap(@js($mapMarkers))">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Explore the map</h2>
                    <p class="text-slate-600 mt-1">Search venues and tackle shops, or click a pin for a quick look.</p>
                </div>
                <a href="{{ route('map.index') }}" class="text-sky-800 font-semibold hover:underline shrink-0">Full map &amp; filters</a>
            </div>

            <div class="flex flex-wrap items-center gap-4 mb-4 text-sm font-semibold text-slate-700">
                <span class="inline-flex items-center gap-2">
                    <span class="inline-block h-3 w-3 rounded-full bg-blue-600 border-2 border-white shadow" aria-hidden="true"></span>
                    Venues
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="inline-block h-3 w-3 rounded-full bg-red-600 border-2 border-white shadow" aria-hidden="true"></span>
                    Tackle shops
                </span>
            </div>

            <div class="relative mb-4 max-w-md">
                <label for="home-venue-search" class="block text-sm font-semibold text-slate-800 mb-1">Search map</label>
                <input
                    id="home-venue-search"
                    type="search"
                    x-model="query"
                    @keydown.escape="query = ''"
                    placeholder="Type a venue or shop name…"
                    class="w-full rounded-md border-2 border-slate-400 bg-white px-3 py-2 focus:border-sky-700 focus:ring-sky-700"
                    autocomplete="off"
                >
                <div
                    x-show="query.trim() && filtered.length"
                    x-cloak
                    class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto bg-white border-2 border-slate-300 rounded-lg shadow-lg"
                >
                    <template x-for="venue in filtered.slice(0, 8)" :key="venue.id">
                        <button
                            type="button"
                            class="w-full text-left px-3 py-2 hover:bg-sky-50 border-b border-slate-100 last:border-0"
                            @click="focusVenue(venue)"
                        >
                            <span class="font-semibold text-slate-900" x-text="venue.name"></span>
                            <span class="block text-xs text-slate-600" x-text="venue.type === 'tackle_shop' ? ('Tackle shop · ' + venue.ticket_type) : venue.ticket_type"></span>
                        </button>
                    </template>
                </div>
                <p x-show="query.trim() && !filtered.length" x-cloak class="mt-2 text-sm text-slate-600">No venues or shops match that name.</p>
            </div>

            <div id="home-venue-map"
                 class="w-full rounded-xl border-2 border-slate-400 overflow-hidden bg-slate-200"
                 style="height: 28rem; min-height: 28rem;"></div>
            <p class="mt-3 text-sm text-slate-600">
                <span x-text="filtered.length"></span> of {{ count($mapMarkers) }} places shown
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Featured venues</h2>
                <p class="text-slate-600 mt-1">Recently approved waters across the region.</p>
            </div>
            <a href="{{ route('venues.index') }}" class="text-sky-800 font-semibold hover:underline">View all</a>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featured as $venue)
                <a href="{{ route('venues.show', $venue) }}" class="block bg-white border-2 border-slate-300 rounded-xl overflow-hidden hover:border-sky-700 transition">
                    @if ($venue->photos->isNotEmpty())
                        <img src="{{ $venue->photos->first()->url() }}" alt="" class="w-full h-40 object-cover border-b-2 border-slate-200">
                    @endif
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-bold text-lg text-slate-900">{{ $venue->name }}</h3>
                            @if ($venue->manager_verified)
                                <span class="shrink-0 text-xs font-bold uppercase tracking-wide bg-emerald-100 text-emerald-900 border border-emerald-600 px-2 py-1 rounded">Verified</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 mt-2 line-clamp-3">{{ $venue->overview ?: 'No overview yet.' }}</p>
                        <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                            <span class="bg-slate-100 border border-slate-300 px-2 py-1 rounded">{{ $venue->ticketTypeLabel() }}</span>
                            @foreach ($venue->allSpecies()->take(3) as $species)
                                <span class="bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">{{ $species->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-slate-600 col-span-full">No venues approved yet. Be the first to submit one.</p>
            @endforelse
        </div>
    </div>

    <div class="border-t-2 border-slate-200 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Angling clubs</h2>
                    <p class="text-slate-600 mt-1">Local clubs and alliances across the region — pick yours when you register.</p>
                </div>
                <a href="{{ route('clubs.index') }}" class="text-sky-800 font-semibold hover:underline">View all</a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($featuredClubs as $club)
                    <x-club-card :club="$club" />
                @empty
                    <p class="text-slate-600 col-span-full">Club listings are coming soon.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="border-t-2 border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Tackle shops</h2>
                    <p class="text-slate-600 mt-1">Local North East independents and the big online stores — with a direct link to each website.</p>
                </div>
                <a href="{{ route('tackle-shops.index') }}" class="text-sky-800 font-semibold hover:underline">View all</a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($featuredShops as $shop)
                    <x-tackle-shop-card :shop="$shop" />
                @empty
                    <p class="text-slate-600 col-span-full">Tackle shop listings are coming soon.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="border-t-2 border-slate-200 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Tackle reviews</h2>
                    <p class="text-slate-600 mt-1">What anglers are using — rated and reviewed by the community.</p>
                </div>
                <a href="{{ route('tackle-reviews.index') }}" class="text-sky-800 font-semibold hover:underline">View all</a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($tackleReviews as $review)
                    <a href="{{ route('tackle-reviews.show', $review) }}" class="block bg-white border-2 border-slate-300 rounded-xl overflow-hidden hover:border-sky-700 transition">
                        @if ($review->photos->isNotEmpty())
                            <img src="{{ $review->photos->first()->url() }}" alt="" class="w-full h-40 object-cover border-b-2 border-slate-200">
                        @endif
                        <div class="p-5">
                            <h3 class="font-bold text-lg text-slate-900">{{ $review->displayName() }}</h3>
                            <div class="mt-2"><x-star-rating :rating="$review->rating" /></div>
                            <p class="text-sm text-slate-700 mt-3 line-clamp-3">{{ $review->body }}</p>
                            <p class="text-xs text-slate-500 mt-3">By {{ $review->user->name }}</p>
                        </div>
                    </a>
                @empty
                    <p class="text-slate-600 col-span-full">
                        No reviews yet.
                        <a href="{{ route('tackle-reviews.create') }}" class="text-sky-800 font-semibold hover:underline">Write the first one</a>.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <style>
            [x-cloak]{display:none!important}
            .leaflet-div-icon.map-pin { background: transparent; border: none; }
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
        <script>
            function homeVenueMap(markers) {
                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');

                const pinIcon = (type) => L.divIcon({
                    className: `leaflet-div-icon map-pin map-pin--${type === 'tackle_shop' ? 'shop' : 'venue'}`,
                    html: '<span class="map-pin__dot"></span>',
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                    popupAnchor: [0, -10],
                });

                return {
                    markers,
                    query: '',
                    map: null,
                    layerGroup: null,
                    pinById: {},
                    get filtered() {
                        const q = this.query.trim().toLowerCase();
                        if (!q) {
                            return this.markers;
                        }

                        return this.markers.filter((marker) => marker.name.toLowerCase().includes(q));
                    },
                    init() {
                        const el = document.getElementById('home-venue-map');
                        if (!el || typeof L === 'undefined') {
                            return;
                        }

                        this.map = L.map(el).setView([54.9, -1.6], 9);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);

                        this.layerGroup = L.layerGroup().addTo(this.map);
                        this.renderMarkers(this.markers);

                        this.$watch('query', () => {
                            this.renderMarkers(this.filtered);
                        });

                        requestAnimationFrame(() => {
                            this.map.invalidateSize();
                            this.renderMarkers(this.filtered);
                        });
                    },
                    popupHtml(marker) {
                        const isShop = marker.type === 'tackle_shop';
                        const species = (marker.species || [])
                            .map((name) => `<span style="display:inline-block;margin:2px 4px 2px 0;padding:2px 6px;border:1px solid #7dd3fc;background:#f0f9ff;border-radius:4px;font-size:11px;font-weight:600;color:#0c4a6e;">${escapeHtml(name)}</span>`)
                            .join('');

                        const verified = !isShop && marker.verified
                            ? '<span style="display:inline-block;margin-left:6px;padding:2px 6px;border:1px solid #059669;background:#d1fae5;border-radius:4px;font-size:10px;font-weight:700;color:#064e3b;text-transform:uppercase;">Verified</span>'
                            : '';

                        const badge = isShop
                            ? '<span style="display:inline-block;margin-left:6px;padding:2px 6px;border:1px solid #dc2626;background:#fef2f2;border-radius:4px;font-size:10px;font-weight:700;color:#7f1d1d;text-transform:uppercase;">Shop</span>'
                            : verified;

                        const ctaLabel = isShop ? 'View shop' : 'View venue';
                        const ctaColor = isShop ? '#dc2626' : '#0369a1';

                        return `
                            <div style="min-width:200px;max-width:260px;font-family:inherit;">
                                <strong style="font-size:15px;color:#0f172a;">${escapeHtml(marker.name)}</strong>${badge}
                                <p style="margin:6px 0 0;font-size:12px;color:#475569;">${escapeHtml(marker.address || 'Address not listed')}</p>
                                <p style="margin:6px 0 0;font-size:12px;font-weight:600;color:#0f172a;">${escapeHtml(marker.ticket_type)}</p>
                                <p style="margin:8px 0 0;font-size:12px;color:#334155;line-height:1.4;">${escapeHtml(marker.overview || 'No summary yet.')}</p>
                                <div style="margin-top:8px;">${species}</div>
                                <a href="${escapeHtml(marker.url)}" style="display:inline-block;margin-top:10px;padding:8px 12px;background:${ctaColor};color:#fff;border-radius:6px;font-size:13px;font-weight:700;text-decoration:none;">${ctaLabel}</a>
                            </div>
                        `;
                    },
                    renderMarkers(list) {
                        this.layerGroup.clearLayers();
                        this.pinById = {};
                        const bounds = [];

                        list.forEach((marker) => {
                            if (marker.lat == null || marker.lng == null) {
                                return;
                            }

                            const pin = L.marker([marker.lat, marker.lng], {
                                icon: pinIcon(marker.type),
                            });
                            pin.bindPopup(this.popupHtml(marker));
                            pin.addTo(this.layerGroup);
                            this.pinById[marker.id] = pin;
                            bounds.push([marker.lat, marker.lng]);
                        });

                        if (bounds.length === 1) {
                            this.map.setView(bounds[0], 12);
                        } else if (bounds.length > 1) {
                            this.map.fitBounds(bounds, { padding: [28, 28] });
                        }
                    },
                    focusVenue(venue) {
                        this.query = venue.name;
                        this.$nextTick(() => {
                            const pin = this.pinById[venue.id];
                            if (!pin) {
                                return;
                            }

                            this.map.setView([venue.lat, venue.lng], 13);
                            pin.openPopup();
                        });
                    }
                };
            }
        </script>
    </x-slot>
</x-app-layout>
