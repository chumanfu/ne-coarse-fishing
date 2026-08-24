<x-app-layout>
    {{-- =====================================================================
         1. HERO
         ===================================================================== --}}
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
                <em>{{ $venueCount }} waters listed across the North East</em>
            </p>
        </div>

        <div class="home-hero__status">
            <span class="home-hero__status-icon" aria-hidden="true"></span>
            <p>On the map today: <strong>{{ count($mapMarkers) }} places to explore</strong></p>
        </div>
    </section>

    {{-- =====================================================================
         2. EDITORIAL INTRO
         ===================================================================== --}}
    <section class="home-intro border-t border-[#d6cfc2]" aria-labelledby="home-intro-heading">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <p class="site-eyebrow mb-3">About this site</p>
            <h2 id="home-intro-heading" class="site-section-title mb-5">
                Built by an angler who moved north and had to start from scratch.
            </h2>

            <div class="grid gap-8 lg:grid-cols-2 lg:gap-12 items-start">
                <div class="space-y-4 text-ink-muted leading-relaxed text-[1.02rem]">
                    <p>For 37 years, coarse angling has been my main passion. In 2011 I moved north to Newcastle — and faced a blank slate. Where do you actually fish around here?</p>
                    <p>Starting fresh in the North East meant bouncing between outdated club websites, scattered Facebook pages, and satellite maps just to find decent water. As a software engineer, that frustration was the catalyst for NE Coarse Fishing.</p>
                    <p>The goal: a dedicated hub for pleasure anglers across the region — venues, clubs, tackle shops, and live session logs, all in one place. The real value comes from the community building it together.</p>
                    <p class="text-ink-soft text-sm">Tight lines,&nbsp; <span class="font-semibold text-ink font-display italic text-base">Chris</span></p>
                </div>

                <figure class="home-intro__photo">
                    <button
                        type="button"
                        class="block w-full cursor-zoom-in"
                        @click="$store.photoLightbox.open(@js([['url' => asset('images/about/canal-fishing-vintage.png'), 'alt' => 'Anglers fishing from a canal bank']]), 0, 'About photo')"
                    >
                        <img
                            src="{{ asset('images/about/canal-fishing-vintage.png') }}"
                            alt="Anglers fishing from a canal bank"
                            class="w-full h-auto object-cover"
                            loading="lazy"
                            width="1200"
                            height="800"
                        >
                    </button>
                    <figcaption>
                        Canal-side fishing — the kind of scene that got many of us started.
                    </figcaption>
                </figure>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('about') }}" class="site-btn site-btn--primary">Read the full story</a>
                <a href="{{ route('venues.index') }}" class="site-btn site-btn--ghost">Browse venues</a>
            </div>
        </div>
    </section>

    {{-- =====================================================================
         3. FEATURED VENUES — full-width card grid
         ===================================================================== --}}
    <section class="home-section home-section--venues home-section--alt border-t border-[#d6cfc2]" aria-labelledby="home-venues-heading">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
            <div class="flex items-end justify-between gap-4 mb-10">
                <div>
                    <p class="site-eyebrow mb-2">Day-ticket &amp; club waters</p>
                    <h2 id="home-venues-heading" class="site-section-title">Featured Venues</h2>
                </div>
                <a href="{{ route('venues.index') }}" class="site-btn site-btn--ghost shrink-0">
                    View all
                </a>
            </div>

            @if ($featured->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featured as $venue)
                        <article class="site-card home-card group flex flex-col">
                            <div class="relative overflow-hidden bg-paper-deep" style="aspect-ratio: 16/9;">
                                @if ($venue->photos->isNotEmpty())
                                    <img
                                        src="{{ $venue->photos->first()->url() }}"
                                        alt="{{ $venue->name }} photo"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-zoom-in"
                                        loading="lazy"
                                        role="button"
                                        tabindex="0"
                                        @click.prevent.stop="$store.photoLightbox.open(@js($venue->photos->map(fn ($p) => ['url' => $p->url(), 'alt' => $venue->name.' photo'])->values()->all()), 0, @js($venue->name.' photo'))"
                                        @keydown.enter.prevent.stop="$store.photoLightbox.open(@js($venue->photos->map(fn ($p) => ['url' => $p->url(), 'alt' => $venue->name.' photo'])->values()->all()), 0, @js($venue->name.' photo'))"
                                        @keydown.space.prevent.stop="$store.photoLightbox.open(@js($venue->photos->map(fn ($p) => ['url' => $p->url(), 'alt' => $venue->name.' photo'])->values()->all()), 0, @js($venue->name.' photo'))"
                                    >
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-moss-soft to-paper-deep flex items-center justify-center">
                                        <span class="text-ink-soft text-sm font-medium">Photo coming soon</span>
                                    </div>
                                @endif
                                @if ($venue->manager_verified)
                                    <span class="absolute top-3 right-3 text-[10px] font-semibold tracking-wide bg-moss-soft text-moss-dark border border-moss/30 px-2.5 py-0.5 rounded-md">Verified</span>
                                @endif
                            </div>

                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-xl font-display text-ink leading-snug mb-1">{{ $venue->name }}</h3>
                                <p class="text-sm text-ink-muted line-clamp-2 flex-1 mb-4">{{ $venue->overview ?: ($venue->address ?: 'North East fishery — more details coming soon.') }}</p>
                                <a href="{{ route('venues.show', $venue) }}" class="site-btn site-btn--ghost self-start text-xs">
                                    View Venue
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-ink-muted">No featured venues yet — <a href="{{ route('venues.create') }}" class="text-water-dark font-semibold underline underline-offset-2 hover:text-moss-dark">submit the first one</a>.</p>
            @endif
        </div>
    </section>

    {{-- =====================================================================
         4. ANGLING CLUBS — full-width card grid
         ===================================================================== --}}
    <section class="home-section home-section--clubs border-t border-[#d6cfc2] bg-paper-bright" aria-labelledby="home-clubs-heading">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
            <div class="flex items-end justify-between gap-4 mb-10">
                <div>
                    <p class="site-eyebrow mb-2">Members &amp; day-ticket clubs</p>
                    <h2 id="home-clubs-heading" class="site-section-title">Angling Clubs</h2>
                </div>
                <a href="{{ route('clubs.index') }}" class="site-btn site-btn--ghost shrink-0">
                    View all
                </a>
            </div>

            @if ($featuredClubs->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredClubs as $club)
                        <article class="site-card home-card group flex flex-col">
                            <div class="relative overflow-hidden bg-moss-soft/60 flex items-center justify-center" style="aspect-ratio: 16/9;">
                                @if ($club->logoUrl())
                                    <img
                                        src="{{ $club->logoUrl() }}"
                                        alt="{{ $club->name }} logo"
                                        class="max-h-28 max-w-[70%] object-contain cursor-zoom-in"
                                        loading="lazy"
                                        role="button"
                                        tabindex="0"
                                        @click.prevent.stop="$store.photoLightbox.open(@js([['url' => $club->logoUrl(), 'alt' => $club->name.' logo']]), 0, @js($club->name.' logo'))"
                                        @keydown.enter.prevent.stop="$store.photoLightbox.open(@js([['url' => $club->logoUrl(), 'alt' => $club->name.' logo']]), 0, @js($club->name.' logo'))"
                                    >
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-water-soft to-paper-deep flex items-center justify-center">
                                        <span class="text-ink-soft text-sm font-medium">Club logo soon</span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-xl font-display text-ink leading-snug mb-1">{{ $club->name }}</h3>
                                <p class="text-sm text-ink-muted line-clamp-2 flex-1 mb-4">{{ $club->overview ?: ($club->town ? 'Based in '.$club->town.'.' : 'Local angling club — details coming soon.') }}</p>
                                <a href="{{ route('clubs.show', $club) }}" class="site-btn site-btn--ghost self-start text-xs">
                                    View Club
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-ink-muted">Club listings are coming soon.</p>
            @endif
        </div>
    </section>

    {{-- =====================================================================
         5. TACKLE SHOPS — full-width card grid
         ===================================================================== --}}
    <section class="home-section home-section--shops home-section--alt border-t border-[#d6cfc2]" aria-labelledby="home-shops-heading">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
            <div class="flex items-end justify-between gap-4 mb-10">
                <div>
                    <p class="site-eyebrow mb-2">Independents &amp; online stores</p>
                    <h2 id="home-shops-heading" class="site-section-title">Tackle Shops</h2>
                </div>
                <a href="{{ route('tackle-shops.index') }}" class="site-btn site-btn--ghost shrink-0">
                    View all
                </a>
            </div>

            @if ($featuredShops->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredShops as $shop)
                        <article class="site-card home-card group flex flex-col">
                            <div class="relative overflow-hidden bg-bank-soft flex items-center justify-center" style="aspect-ratio: 16/9;">
                                @if ($shop->logoUrl())
                                    <img
                                        src="{{ $shop->logoUrl() }}"
                                        alt="{{ $shop->name }} logo"
                                        class="max-h-28 max-w-[70%] object-contain cursor-zoom-in"
                                        loading="lazy"
                                        role="button"
                                        tabindex="0"
                                        @click.prevent.stop="$store.photoLightbox.open(@js([['url' => $shop->logoUrl(), 'alt' => $shop->name.' logo']]), 0, @js($shop->name.' logo'))"
                                        @keydown.enter.prevent.stop="$store.photoLightbox.open(@js([['url' => $shop->logoUrl(), 'alt' => $shop->name.' logo']]), 0, @js($shop->name.' logo'))"
                                    >
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-bank-soft to-paper-deep flex items-center justify-center">
                                        <span class="text-ink-soft text-sm font-medium">Shop logo soon</span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-xl font-display text-ink leading-snug mb-1">{{ $shop->name }}</h3>
                                <p class="text-sm text-ink-muted line-clamp-2 flex-1 mb-4">{{ $shop->overview ?: ($shop->town ? $shop->locationTypeLabel().' · '.$shop->town : $shop->locationTypeLabel()) }}</p>
                                <a href="{{ route('tackle-shops.show', $shop) }}" class="site-btn site-btn--ghost self-start text-xs">
                                    View Shop
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-ink-muted">Tackle shop listings are coming soon.</p>
            @endif
        </div>
    </section>

    {{-- =====================================================================
         6. MAP + ACTIVITY FEED
         ===================================================================== --}}
    <section class="home-board border-t border-[#d6cfc2]" aria-label="Home board">
        <div class="home-board__inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12">

            <aside class="mb-8">
                <div class="home-board__panel">
                    <div class="home-board__panel-head">
                        <div>
                            <h2 class="home-board__title">Latest activity</h2>
                            <p class="home-board__lead">New venues, sessions, tactics and more.</p>
                        </div>
                        <a href="{{ route('activity.index') }}" class="home-board__link">View all</a>
                    </div>

                    <div class="home-activity-carousel flex gap-3 overflow-x-auto pb-1 -mx-1 px-1">
                        @forelse ($activities as $activity)
                            <div class="home-activity-carousel__item flex-shrink-0 w-56">
                                <x-activity-row :activity="$activity" compact />
                            </div>
                        @empty
                            <p class="text-sm text-ink-muted">No activity yet — log a session or add a venue to get things started.</p>
                        @endforelse
                    </div>
                </div>
            </aside>

            <div
                class="home-board__col home-board__col--map space-y-8"
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

                        <div class="flex flex-wrap items-center gap-4 mb-3 text-sm font-semibold text-ink-muted">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full bg-water border-2 border-white shadow" aria-hidden="true"></span>
                                Venues
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full bg-bank border-2 border-white shadow" aria-hidden="true"></span>
                                Tackle shops
                            </span>
                        </div>

                        <div class="relative mb-3">
                            <label for="home-venue-search" class="block text-sm font-semibold text-ink mb-1">Search map</label>
                            <input
                                id="home-venue-search"
                                type="search"
                                x-model="query"
                                @keydown.escape="query = ''"
                                placeholder="Type a venue or shop name…"
                                class="w-full rounded-lg border border-[#c4bbad] bg-paper-bright px-3 py-2 focus:border-water focus:ring-water"
                                autocomplete="off"
                            >
                            <div
                                x-show="query.trim() && filtered.length"
                                x-cloak
                                class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto bg-paper-bright border border-[#d6cfc2] rounded-lg shadow-lift"
                            >
                                <template x-for="venue in filtered.slice(0, 8)" :key="venue.id">
                                    <button
                                        type="button"
                                        class="w-full text-left px-3 py-2 hover:bg-moss-soft border-b border-[#ebe6db] last:border-0"
                                        @click="focusVenue(venue)"
                                    >
                                        <span class="font-semibold text-ink" x-text="venue.name"></span>
                                        <span class="block text-xs text-ink-muted" x-text="venue.type === 'tackle_shop' ? ('Tackle shop · ' + venue.ticket_type) : venue.ticket_type"></span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="query.trim() && !filtered.length" x-cloak class="mt-2 text-sm text-ink-muted">No venues or shops match that name.</p>
                        </div>

                        <div id="home-venue-map"
                             class="w-full rounded-xl border border-[#c4bbad] overflow-hidden bg-paper-deep"
                             style="height: 22rem; min-height: 22rem;"></div>
                        <p class="mt-2 text-sm text-ink-muted">
                            <span x-text="filtered.length"></span> of {{ count($mapMarkers) }} places shown
                        </p>

                        <div class="mt-4 pt-4 border-t border-[#d6cfc2]" aria-label="Weather along the bank">
                            <p class="site-eyebrow mb-2">Weather along the bank</p>
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
                                <p class="text-xs text-ink-soft">Weather is unavailable right now.</p>
                            @endif
                        </div>
                    </div>
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
                box-shadow: 0 1px 4px rgba(31, 46, 38, 0.4);
            }
            .map-pin--venue .map-pin__dot { background: #4a7c8c; }
            .map-pin--shop .map-pin__dot { background: #6b5746; }
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

                        setTimeout(() => this.map?.invalidateSize(), 200);
                        setTimeout(() => this.map?.invalidateSize(), 600);
                    },
                    popupHtml(marker) {
                        const isShop = marker.type === 'tackle_shop';
                        const species = (marker.species || [])
                            .map((name) => `<span style="display:inline-block;margin:2px 4px 2px 0;padding:2px 6px;border:1px solid #a8c9d4;background:#e7f1f4;border-radius:4px;font-size:11px;font-weight:600;color:#356575;">${escapeHtml(name)}</span>`)
                            .join('');

                        const verified = !isShop && marker.verified
                            ? '<span style="display:inline-block;margin-left:6px;padding:2px 6px;border:1px solid #3f5c48;background:#e6eee8;border-radius:4px;font-size:10px;font-weight:700;color:#2c4234;">Verified</span>'
                            : '';

                        const badge = isShop
                            ? '<span style="display:inline-block;margin-left:6px;padding:2px 6px;border:1px solid #6b5746;background:#f0ebe3;border-radius:4px;font-size:10px;font-weight:700;color:#6b5746;">Shop</span>'
                            : verified;

                        const ctaLabel = isShop ? 'View shop' : 'View venue';
                        const ctaColor = isShop ? '#6b5746' : '#3f5c48';

                        return `
                            <div style="min-width:200px;max-width:260px;font-family:inherit;">
                                <strong style="font-size:15px;color:#1f2e26;">${escapeHtml(marker.name)}</strong>${badge}
                                <p style="margin:6px 0 0;font-size:12px;color:#4d5f55;">${escapeHtml(marker.address || 'Address not listed')}</p>
                                <p style="margin:6px 0 0;font-size:12px;font-weight:600;color:#1f2e26;">${escapeHtml(marker.ticket_type)}</p>
                                <p style="margin:8px 0 0;font-size:12px;color:#4d5f55;line-height:1.4;">${escapeHtml(marker.overview || 'No summary yet.')}</p>
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
