<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wide bg-slate-100 border border-slate-400 px-2 py-1 rounded">{{ $venue->ticketTypeLabel() }}</span>
                    @if ($venue->is_complex)
                        <span class="text-xs font-bold uppercase tracking-wide bg-amber-50 border border-amber-500 text-amber-950 px-2 py-1 rounded">Complex</span>
                    @endif
                    @if ($venue->manager_verified)
                        <span class="text-xs font-bold uppercase tracking-wide bg-emerald-100 border border-emerald-600 text-emerald-900 px-2 py-1 rounded">Manager verified</span>
                    @endif
                    @unless ($venue->is_approved)
                        <span class="text-xs font-bold uppercase tracking-wide bg-orange-100 border border-orange-600 text-orange-950 px-2 py-1 rounded">Pending approval</span>
                    @endunless
                </div>
                <h1 class="text-3xl font-bold text-slate-900">{{ $venue->name }}</h1>
                @if ($venue->address)
                    <p class="text-slate-600 mt-1">{{ $venue->address }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('map.index', ['species' => null]) }}#venue-{{ $venue->id }}" class="px-3 py-2 rounded-md border-2 border-slate-800 font-semibold text-sm">View on map</a>
                @auth
                    <a href="{{ route('sessions.create', ['venue' => $venue->slug]) }}" class="px-3 py-2 rounded-md bg-sky-700 text-white font-semibold text-sm">Log a session here</a>
                    @can('manage', $venue)
                        <a href="{{ route('venues.edit', $venue) }}" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Edit venue</a>
                        <a href="{{ route('match-reports.create', $venue) }}" class="px-3 py-2 rounded-md border-2 border-emerald-700 text-emerald-900 font-semibold text-sm">Match report</a>
                        <a href="{{ route('announcements.create', $venue) }}" class="px-3 py-2 rounded-md border-2 border-emerald-700 text-emerald-900 font-semibold text-sm">Announcement</a>
                    @elsecan('claim', $venue)
                        <form method="POST" action="{{ route('venues.claim', $venue) }}" class="inline">
                            @csrf
                            <button class="px-3 py-2 rounded-md border-2 border-amber-600 text-amber-950 font-semibold text-sm" onclick="return confirm('Claim management of this venue?')">Claim ownership</button>
                        </form>
                    @endcan
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="text-xl font-bold mb-3">Overview</h2>
                <p class="text-slate-800 whitespace-pre-line">{{ $venue->overview ?: 'No overview provided yet.' }}</p>
            </section>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="text-xl font-bold mb-4">Waters</h2>
                <div class="space-y-4">
                    @foreach ($venue->waters as $water)
                        <div class="border-2 border-slate-200 rounded-lg p-4">
                            <h3 class="font-bold text-slate-900">{{ $water->name }}</h3>
                            @if ($water->description)
                                <p class="text-sm text-slate-700 mt-1 whitespace-pre-line">{{ $water->description }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-3 text-sm text-slate-700">
                                @if ($water->peg_count)
                                    <span><strong>Pegs:</strong> {{ $water->peg_count }}</span>
                                @endif
                                @if ($water->depth_info)
                                    <span><strong>Depth:</strong> {{ $water->depth_info }}</span>
                                @endif
                            </div>
                            @if ($water->species->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($water->species as $species)
                                        <span class="text-xs font-semibold bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">{{ $species->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="text-xl font-bold mb-3">Tactics &amp; local knowledge</h2>
                <p class="text-slate-800 whitespace-pre-line">{{ $venue->tactics_guide ?: 'No tactics guide yet — share what works on your next session.' }}</p>
            </section>

            <section id="official" class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="text-xl font-bold mb-1">Official announcements &amp; match reports</h2>
                <p class="text-sm text-slate-600 mb-4">Posted by verified fishery managers and club admins.</p>

                <div class="space-y-4">
                    @forelse ($venue->announcements->concat($venue->matchReports)->sortByDesc('published_at') as $item)
                        <article class="border-l-4 border-emerald-600 pl-4 py-1">
                            <div class="flex flex-wrap gap-2 items-center mb-1">
                                <span class="text-xs font-bold uppercase tracking-wide text-emerald-800">
                                    {{ $item instanceof \App\Models\MatchReport ? 'Match Report' : $item->typeLabel() }}
                                </span>
                                <span class="text-xs text-slate-500">{{ optional($item->published_at)->format('d M Y') }}</span>
                            </div>
                            <h3 class="font-bold text-slate-900">{{ $item->title }}</h3>
                            <p class="text-slate-800 mt-1 whitespace-pre-line">{{ $item->body }}</p>
                        </article>
                    @empty
                        <p class="text-slate-600">No official posts yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Recent sessions</h2>
                </div>
                <div class="space-y-4">
                    @forelse ($venue->fishingSessions as $session)
                        <div class="border-2 border-slate-200 rounded-lg p-4">
                            <div class="flex flex-wrap justify-between gap-2">
                                <p class="font-semibold text-slate-900">{{ $session->user->name }} · {{ $session->fished_at->format('d M Y') }}</p>
                                @if ($session->water)
                                    <p class="text-sm text-slate-600">{{ $session->water->name }}@if($session->peg_number) · Peg {{ $session->peg_number }}@endif</p>
                                @endif
                            </div>
                            @if ($session->commentary)
                                <p class="text-sm text-slate-800 mt-2">{{ Str::limit($session->commentary, 220) }}</p>
                            @endif
                            @if ($session->catches->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($session->catches as $catch)
                                        <span class="text-xs font-semibold bg-slate-100 border border-slate-300 px-2 py-1 rounded">
                                            {{ $catch->species->name }}
                                            @if ($catch->weight_lb) · {{ $catch->weight_lb }}lb @endif
                                            @if ($catch->bait) · {{ $catch->bait }} @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            @if ($session->photos->isNotEmpty())
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    @foreach ($session->photos->take(3) as $photo)
                                        <img src="{{ $photo->url() }}" alt="Session photo" class="rounded-md object-cover h-24 w-full border border-slate-300">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-slate-600">No public sessions logged here yet.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <div id="venue-map" class="h-64 rounded-xl border-2 border-slate-400 overflow-hidden"></div>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4 text-sm">
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Day tickets</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->day_ticket_info ?: 'Not listed.' }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Membership</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->membership_info ?: 'Not listed.' }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Opening times</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->opening_times ?: 'Not listed.' }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Seasonal restrictions</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->season_info ?: 'Not listed.' }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Directions / parking</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->directions ?: 'Not listed.' }}</p>
                </div>
                @if ($speciesList->isNotEmpty())
                    <div>
                        <h3 class="font-bold text-slate-900 mb-2">Species on site</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($speciesList as $species)
                                <span class="text-xs font-semibold bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">{{ $species->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </aside>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const map = L.map('venue-map').setView([{{ $venue->latitude }}, {{ $venue->longitude }}], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                L.marker([{{ $venue->latitude }}, {{ $venue->longitude }}]).addTo(map)
                    .bindPopup(@json($venue->name));
            });
        </script>
    @endpush
</x-app-layout>
