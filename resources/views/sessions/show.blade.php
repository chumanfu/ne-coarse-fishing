<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $session->venue->name }}</h1>
                <p class="text-slate-600 mt-1">{{ $session->fished_at->format('d M Y') }} · logged by {{ $session->user->name }}</p>
            </div>
            @if ($canManage)
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('sessions.edit', $session) }}" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Edit</a>
                    <form method="POST" action="{{ route('sessions.destroy', $session) }}" onsubmit="return confirm('Delete this session?')">
                        @csrf
                        @method('DELETE')
                        <button class="px-3 py-2 rounded-md border-2 border-red-700 text-red-800 font-semibold text-sm">Delete</button>
                    </form>
                </div>
            @endif
        </div>
        <div class="mt-4">
            <x-share-links
                :url="route('sessions.show', $session)"
                :title="$session->venue->name.' session'"
                :text="'Fishing session at '.$session->venue->name.' on '.$session->fished_at->format('d M Y')"
                label="Share session"
            />
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <section class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-2 text-sm">
            @if ($session->water) <p><strong>Water:</strong> {{ $session->water->name }}</p> @endif
            @if ($session->pegLabel()) <p><strong>Peg:</strong> {{ $session->pegLabel() }}</p> @endif
            @if ($session->weather) <p><strong>Weather:</strong> {{ $session->weather }}</p> @endif
            @if ($session->duration_hours) <p><strong>Duration:</strong> {{ $session->duration_hours }} hours</p> @endif
        </section>

        @if ($session->hasPegLocation())
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="font-bold text-lg mb-3">Peg location</h2>
                @if ($session->pegLabel())
                    <p class="text-sm text-slate-600 mb-3">{{ $session->pegLabel() }}</p>
                @endif
                <div id="session-peg-view-map"
                     class="w-full rounded-lg border-2 border-slate-400 overflow-hidden bg-slate-200"
                     style="height: 18rem; min-height: 18rem;"></div>
                <p class="text-xs text-slate-500 mt-2">
                    {{ number_format($session->pegMapLatitude(), 5) }}, {{ number_format($session->pegMapLongitude(), 5) }}
                </p>
                @if ($session->waterPeg?->photos?->isNotEmpty())
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($session->waterPeg->photos as $photo)
                            <a href="{{ $photo->url() }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $photo->url() }}" alt="Peg {{ $session->pegLabel() }} photo" class="rounded-lg object-cover h-28 w-full border border-slate-300 hover:border-sky-700">
                            </a>
                        @endforeach
                    </div>
                    @unless ($session->waterPeg->is_verified)
                        <p class="text-xs text-amber-800 mt-2 font-semibold">Peg photos awaiting venue owner verification.</p>
                    @endunless
                @endif
            </section>
        @endif

        @if ($session->commentary)
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="font-bold text-lg mb-2">Write-up</h2>
                <p class="whitespace-pre-line text-slate-800">{{ $session->commentary }}</p>
            </section>
        @endif

        @if ($session->tactics_tip || $session->venueTactic)
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
                    <h2 class="font-bold text-lg">Tactics tip</h2>
                    @if ($session->venueTactic)
                        @can('update', $session->venueTactic)
                            <a href="{{ route('tactics.edit', $session->venueTactic) }}" class="text-sm font-semibold text-sky-800 hover:underline">Edit tactic</a>
                        @endcan
                    @endif
                </div>
                <p class="whitespace-pre-line text-slate-800">{{ $session->venueTactic?->body ?? $session->tactics_tip }}</p>
                <p class="text-sm text-slate-600 mt-2">Shared on the <a href="{{ route('venues.show', $session->venue) }}" class="text-sky-800 font-semibold hover:underline">{{ $session->venue->name }}</a> tactics guide.</p>
            </section>
        @endif

        @if ($session->catches->isNotEmpty())
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="font-bold text-lg mb-3">Catches</h2>
                <ul class="space-y-2">
                    @foreach ($session->catches as $catch)
                        <li class="border-2 border-slate-200 rounded-lg px-3 py-2 text-sm">
                            <strong>{{ $catch->species->name }}</strong>
                            @if ($catch->quantity > 1) × {{ $catch->quantity }} @endif
                            @if ($catch->weight_lb) · {{ $catch->weight_lb }}lb @endif
                            @if ($catch->bait) · {{ $catch->bait }} @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($session->photos->isNotEmpty())
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="font-bold text-lg mb-3">Photos</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($session->photos as $photo)
                        <img src="{{ $photo->url() }}" alt="Session photo" class="rounded-lg object-cover h-40 w-full border border-slate-300">
                    @endforeach
                </div>
            </section>
        @endif

        <a href="{{ route('venues.show', $session->venue) }}" class="inline-flex font-semibold text-sky-800 hover:underline">Back to venue</a>
    </div>

    @if ($session->hasPegLocation())
        <x-slot name="scripts">
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const lat = {{ $session->pegMapLatitude() }};
                    const lng = {{ $session->pegMapLongitude() }};
                    const map = L.map('session-peg-view-map').setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);
                    L.marker([lat, lng]).addTo(map)
                        .bindPopup(@json($session->pegLabel() ? 'Peg '.$session->pegLabel() : 'Peg location'))
                        .openPopup();
                    setTimeout(() => map.invalidateSize(), 200);
                });
            </script>
        </x-slot>
    @endif
</x-app-layout>
