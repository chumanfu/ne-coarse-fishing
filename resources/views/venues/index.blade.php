<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Venue directory</h1>
                <p class="text-slate-600 mt-1">Day tickets, club waters and complexes across the North East.</p>
            </div>
            @auth
                <a href="{{ route('venues.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-sky-700 text-white font-semibold hover:bg-sky-800">Submit a venue</a>
            @endauth
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="GET" class="bg-white border-2 border-slate-300 rounded-xl p-4 mb-6 grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-800 mb-1">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Venue name or area" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-1">Species</label>
                <select name="species" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Any</option>
                    @foreach ($species as $item)
                        <option value="{{ $item->slug }}" @selected(($filters['species'] ?? '') === $item->slug)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-1">Ticket type</label>
                <select name="ticket_type" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Any</option>
                    <option value="day_ticket" @selected(($filters['ticket_type'] ?? '') === 'day_ticket')>Day Ticket</option>
                    <option value="club" @selected(($filters['ticket_type'] ?? '') === 'club')>Club</option>
                    <option value="syndicate" @selected(($filters['ticket_type'] ?? '') === 'syndicate')>Syndicate</option>
                    <option value="mixed" @selected(($filters['ticket_type'] ?? '') === 'mixed')>Mixed</option>
                </select>
            </div>
            <div class="sm:col-span-4 flex gap-2">
                <button class="px-4 py-2 rounded-md bg-slate-900 text-white font-semibold">Filter</button>
                <a href="{{ route('venues.index') }}" class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold text-slate-800">Reset</a>
            </div>
        </form>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($venues as $venue)
                <x-venue-card :venue="$venue" />
            @empty
                <p class="col-span-full text-slate-600">No venues match those filters.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $venues->links() }}</div>
    </div>
</x-app-layout>
