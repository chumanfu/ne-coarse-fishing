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

    <section class="home-board border-t border-slate-200 bg-slate-50" aria-label="Home board">
        <div class="home-board__inner max-w-[100rem] mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12">
            <section class="home-weather mb-8" aria-labelledby="home-weather-heading">
                <div class="home-board__panel">
                    <div class="home-board__panel-head">
                        <div>
                            <h2 id="home-weather-heading" class="home-board__title">Weather along the bank</h2>
                            <p class="home-board__lead">Live conditions from Berwick down to Thirsk.</p>
                        </div>
                    </div>

                    @if (count($weatherLocations) > 0)
                        <ul class="home-weather__grid">
                            @foreach ($weatherLocations as $place)
                                <li class="home-weather__place">
                                    <span class="home-weather__icon home-weather__icon--{{ $place['icon'] }}" aria-hidden="true"></span>
                                    <div class="home-weather__meta">
                                        <p class="home-weather__name">{{ $place['name'] }}</p>
                                        <p class="home-weather__condition">{{ $place['condition'] }}</p>
                                        @if (! empty($place['outlook']))
                                            <p class="home-weather__outlook">{{ $place['outlook'] }}</p>
                                        @endif
                                    </div>
                                    <div class="home-weather__figures">
                                        <p class="home-weather__temp">{{ $place['temperature_c'] }}°C</p>
                                        <p class="home-weather__wind">{{ $place['wind_mph'] }} mph</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-600">
                            Weather is unavailable right now — check again shortly before you head out.
                        </p>
                    @endif
                </div>
            </section>

            <div class="home-board__grid grid gap-8 xl:grid-cols-12 xl:gap-6 2xl:gap-8 xl:items-start">

                {{-- Column 1: Latest activity --}}
                <aside class="home-board__col home-board__col--activity xl:col-span-3 order-2 xl:order-1">
                    <div class="home-board__panel">
                        <div class="home-board__panel-head">
                            <div>
                                <h2 class="home-board__title">Latest activity</h2>
                                <p class="home-board__lead">New venues, sessions, tactics and more.</p>
                            </div>
                            <a href="{{ route('activity.index') }}" class="home-board__link">View all</a>
                        </div>

                        <div class="space-y-2">
                            @forelse ($activities as $activity)
                                <x-activity-row :activity="$activity" compact />
                            @empty
                                <p class="text-sm text-slate-600">No activity yet — log a session or add a venue to get things started.</p>
                            @endforelse
                        </div>
                    </div>
                </aside>

                {{-- Column 2: Map + featured venues --}}
                <div
                    class="home-board__col home-board__col--map xl:col-span-5 2xl:col-span-6 order-1 xl:order-2 space-y-8"
                    id="home-map"
                    x-data="homeVenueMap(@js($mapMarkers))"
                >
                    <div class="home-board__panel">
                        <div class="home-board__panel-head">
                            <div>
                                <h2 class="home-board__title">Explore the map</h2>
                                <p class="home-board__lead">Search venues and tackle shops, or click a pin.</p>
                            </div>
                            <a href="{{ route('map.index') }}" class="home-board__link">Full map</a>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 mb-3 text-sm font-semibold text-slate-700">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full bg-blue-600 border-2 border-white shadow" aria-hidden="true"></span>
                                Venues
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full bg-red-600 border-2 border-white shadow" aria-hidden="true"></span>
                                Tackle shops
                            </span>
                        </div>

                        <div class="relative mb-3">
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
                             style="height: 22rem; min-height: 22rem;"></div>
                        <p class="mt-2 text-sm text-slate-600">
                            <span x-text="filtered.length"></span> of {{ count($mapMarkers) }} places shown
                        </p>
                    </div>

                    <div class="home-board__panel">
                        <div class="home-board__panel-head">
                            <div>
                                <h2 class="home-board__title">Featured venues</h2>
                                <p class="home-board__lead">Recently approved waters across the region.</p>
                            </div>
                            <a href="{{ route('venues.index') }}" class="home-board__link">View all</a>
                        </div>

                        <div class="space-y-3">
                            @forelse ($featured as $venue)
                                <a href="{{ route('venues.show', $venue) }}" class="home-board__item group">
                                    @if ($venue->photos->isNotEmpty())
                                        <img src="{{ $venue->photos->first()->url() }}" alt="" class="home-board__thumb" loading="lazy">
                                    @else
                                        <span class="home-board__thumb home-board__thumb--empty" aria-hidden="true"></span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="font-bold text-slate-900 group-hover:text-sky-800">{{ $venue->name }}</h3>
                                            @if ($venue->manager_verified)
                                                <span class="shrink-0 text-[10px] font-bold uppercase tracking-wide bg-emerald-100 text-emerald-900 border border-emerald-600 px-1.5 py-0.5 rounded">Verified</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ $venue->overview ?: 'No overview yet.' }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5 text-[11px] font-semibold">
                                            <span class="bg-slate-100 border border-slate-300 px-1.5 py-0.5 rounded">{{ $venue->ticketTypeLabel() }}</span>
                                            @foreach ($venue->allSpecies()->take(2) as $species)
                                                <span class="bg-sky-50 border border-sky-300 text-sky-900 px-1.5 py-0.5 rounded">{{ $species->name }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <p class="text-sm text-slate-600">No venues approved yet. Be the first to submit one.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Column 3: Clubs, shops, reviews --}}
                <aside class="home-board__col home-board__col--directory xl:col-span-4 2xl:col-span-3 order-3 space-y-8">
                    <div class="home-board__panel">
                        <div class="home-board__panel-head">
                            <div>
                                <h2 class="home-board__title">Angling clubs</h2>
                                <p class="home-board__lead">Local clubs across the region.</p>
                            </div>
                            <a href="{{ route('clubs.index') }}" class="home-board__link">View all</a>
                        </div>

                        <div class="space-y-2">
                            @forelse ($featuredClubs as $club)
                                <a href="{{ route('clubs.show', $club) }}" class="home-board__item home-board__item--compact group">
                                    @if ($club->logoUrl())
                                        <img src="{{ $club->logoUrl() }}" alt="" class="home-board__logo" loading="lazy">
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-bold text-slate-900 group-hover:text-sky-800">{{ $club->name }}</h3>
                                        @if ($club->town)
                                            <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ $club->town }}</p>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <p class="text-sm text-slate-600">Club listings are coming soon.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="home-board__panel">
                        <div class="home-board__panel-head">
                            <div>
                                <h2 class="home-board__title">Tackle shops</h2>
                                <p class="home-board__lead">Independents and online stores.</p>
                            </div>
                            <a href="{{ route('tackle-shops.index') }}" class="home-board__link">View all</a>
                        </div>

                        <div class="space-y-2">
                            @forelse ($featuredShops as $shop)
                                <a href="{{ route('tackle-shops.show', $shop) }}" class="home-board__item home-board__item--compact group">
                                    @if ($shop->logoUrl())
                                        <img src="{{ $shop->logoUrl() }}" alt="" class="home-board__logo" loading="lazy">
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-bold text-slate-900 group-hover:text-sky-800">{{ $shop->name }}</h3>
                                        <p class="text-xs font-semibold text-slate-500 mt-0.5">
                                            {{ $shop->locationTypeLabel() }}@if ($shop->town) · {{ $shop->town }}@endif
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <p class="text-sm text-slate-600">Tackle shop listings are coming soon.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="home-board__panel">
                        <div class="home-board__panel-head">
                            <div>
                                <h2 class="home-board__title">Tackle reviews</h2>
                                <p class="home-board__lead">Rated by the community.</p>
                            </div>
                            <a href="{{ route('tackle-reviews.index') }}" class="home-board__link">View all</a>
                        </div>

                        <div class="space-y-2">
                            @forelse ($tackleReviews as $review)
                                <a href="{{ route('tackle-reviews.show', $review) }}" class="home-board__item home-board__item--compact group">
                                    @if ($review->photos->isNotEmpty())
                                        <img src="{{ $review->photos->first()->url() }}" alt="" class="home-board__thumb home-board__thumb--square" loading="lazy">
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-bold text-slate-900 group-hover:text-sky-800">{{ $review->displayName() }}</h3>
                                        <div class="mt-1"><x-star-rating :rating="$review->rating" /></div>
                                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $review->body }}</p>
                                    </div>
                                </a>
                            @empty
                                <p class="text-sm text-slate-600">
                                    No reviews yet.
                                    <a href="{{ route('tackle-reviews.create') }}" class="text-sky-800 font-semibold hover:underline">Write the first one</a>.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </section>

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

            .home-board__panel {
                background: #fff;
                border: 2px solid #cbd5e1;
                border-radius: 0.75rem;
                padding: 1rem 1.1rem 1.15rem;
            }
            .home-board__panel-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.75rem;
                margin-bottom: 0.9rem;
            }
            .home-board__title {
                font-size: 1.15rem;
                line-height: 1.25;
                font-weight: 700;
                color: #0f172a;
            }
            .home-board__lead {
                margin-top: 0.2rem;
                font-size: 0.875rem;
                color: #475569;
            }
            .home-board__link {
                flex-shrink: 0;
                font-size: 0.875rem;
                font-weight: 600;
                color: #075985;
                text-decoration: none;
            }
            .home-board__link:hover { text-decoration: underline; }
            .home-board__item {
                display: flex;
                gap: 0.75rem;
                align-items: flex-start;
                padding: 0.7rem;
                border: 2px solid #e2e8f0;
                border-radius: 0.65rem;
                background: #f8fafc;
                transition: border-color 0.15s ease;
            }
            .home-board__item:hover { border-color: #0369a1; }
            .home-board__item--compact {
                align-items: center;
                padding: 0.55rem 0.65rem;
            }
            .home-board__thumb {
                width: 4.5rem;
                height: 4.5rem;
                object-fit: cover;
                border-radius: 0.45rem;
                border: 1px solid #cbd5e1;
                flex-shrink: 0;
                background: #e2e8f0;
            }
            .home-board__thumb--square {
                width: 3.25rem;
                height: 3.25rem;
            }
            .home-board__thumb--empty {
                display: block;
                background:
                    linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            }
            .home-board__logo {
                width: 2.75rem;
                height: 2.75rem;
                object-fit: contain;
                border-radius: 0.4rem;
                border: 1px solid #e2e8f0;
                background: #fff;
                padding: 0.2rem;
                flex-shrink: 0;
            }
            @media (min-width: 1280px) {
                .home-board__col--activity,
                .home-board__col--directory {
                    position: sticky;
                    top: 1rem;
                }
            }

            .home-weather__grid {
                display: grid;
                gap: 0.65rem;
                grid-template-columns: 1fr;
                margin: 0;
                padding: 0;
                list-style: none;
            }
            @media (min-width: 640px) {
                .home-weather__grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (min-width: 1024px) {
                .home-weather__grid {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
            }
            @media (min-width: 1280px) {
                .home-weather__grid {
                    grid-template-columns: repeat(7, minmax(0, 1fr));
                }
            }
            .home-weather__place {
                display: flex;
                align-items: flex-start;
                gap: 0.55rem;
                padding: 0.65rem 0.7rem;
                border: 2px solid #e2e8f0;
                border-radius: 0.65rem;
                background: #f8fafc;
            }
            .home-weather__icon {
                width: 1.65rem;
                height: 1.65rem;
                flex-shrink: 0;
                margin-top: 0.1rem;
                border-radius: 999px;
                border: 2px solid #94a3b8;
                background: #e2e8f0;
                position: relative;
            }
            .home-weather__icon--sun {
                border-color: #ca8a04;
                background: radial-gradient(circle at 50% 50%, #fde047 0 42%, #fef9c3 43% 100%);
            }
            .home-weather__icon--sun-cloud {
                border-color: #64748b;
                background:
                    radial-gradient(circle at 30% 35%, #fde047 0 28%, transparent 29%),
                    linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
            }
            .home-weather__icon--cloud {
                border-color: #64748b;
                background: linear-gradient(180deg, #e2e8f0 0%, #94a3b8 100%);
            }
            .home-weather__icon--rain {
                border-color: #0369a1;
                background:
                    linear-gradient(180deg, #e2e8f0 0 45%, transparent 45%),
                    repeating-linear-gradient(135deg, transparent 0 3px, #38bdf8 3px 5px),
                    #bae6fd;
            }
            .home-weather__icon--snow {
                border-color: #64748b;
                background:
                    radial-gradient(circle at 30% 55%, #fff 0 12%, transparent 13%),
                    radial-gradient(circle at 65% 70%, #fff 0 10%, transparent 11%),
                    linear-gradient(180deg, #e2e8f0 0%, #cbd5e1 100%);
            }
            .home-weather__icon--thunder {
                border-color: #a16207;
                background:
                    linear-gradient(135deg, transparent 42%, #facc15 42% 52%, transparent 52%),
                    linear-gradient(180deg, #94a3b8 0%, #475569 100%);
            }
            .home-weather__meta {
                min-width: 0;
                flex: 1;
            }
            .home-weather__name {
                font-size: 0.9rem;
                font-weight: 700;
                color: #0f172a;
                line-height: 1.2;
            }
            .home-weather__condition {
                margin-top: 0.15rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: #475569;
            }
            .home-weather__outlook {
                margin-top: 0.2rem;
                font-size: 0.7rem;
                color: #64748b;
                line-height: 1.3;
            }
            .home-weather__figures {
                text-align: right;
                flex-shrink: 0;
            }
            .home-weather__temp {
                font-size: 1.05rem;
                font-weight: 700;
                color: #0f172a;
                line-height: 1.1;
            }
            .home-weather__wind {
                margin-top: 0.15rem;
                font-size: 0.7rem;
                font-weight: 600;
                color: #64748b;
            }
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

                        // Recalculate once the three-column layout settles.
                        setTimeout(() => this.map?.invalidateSize(), 200);
                        setTimeout(() => this.map?.invalidateSize(), 600);
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
