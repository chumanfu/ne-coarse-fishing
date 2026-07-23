<x-app-layout>
    <div class="relative overflow-hidden bg-gradient-to-br from-sky-900 via-slate-900 to-emerald-900 text-white">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #38bdf8 0, transparent 35%), radial-gradient(circle at 80% 0%, #34d399 0, transparent 30%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <p class="text-sky-200 font-semibold tracking-wide uppercase text-sm mb-3">North East · Coarse Angling</p>
            <h1 class="text-4xl sm:text-5xl font-bold tracking-tight max-w-3xl leading-tight">
                Find the best waters from the Tyne to the Tees
            </h1>
            <p class="mt-4 text-lg text-slate-200 max-w-2xl">
                Discover day-ticket lakes, club complexes and canal stretches. Read local tactics, log your sessions, and follow official match reports.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('map.index') }}" class="inline-flex items-center px-5 py-3 rounded-md bg-white text-slate-900 font-bold hover:bg-sky-50">Open the map</a>
                <a href="{{ route('venues.index') }}" class="inline-flex items-center px-5 py-3 rounded-md border-2 border-white text-white font-bold hover:bg-white/10">Browse venues</a>
            </div>
            <p class="mt-6 text-sm text-sky-100 font-medium">{{ $venueCount }} approved venues on the portal</p>
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
                <a href="{{ route('venues.show', $venue) }}" class="block bg-white border-2 border-slate-300 rounded-xl p-5 hover:border-sky-700 transition">
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
                </a>
            @empty
                <p class="text-slate-600 col-span-full">No venues approved yet. Be the first to submit one.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
