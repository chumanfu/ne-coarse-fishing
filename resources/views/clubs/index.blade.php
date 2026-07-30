<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Angling clubs</h1>
            <p class="text-slate-600 mt-1">North East clubs and alliances — find contacts and mark your memberships when you register.</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="GET" class="bg-white border-2 border-slate-300 rounded-xl p-4 mb-6 grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-3">
                <label class="block text-sm font-semibold text-slate-800 mb-1">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Club name or town" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
            </div>
            <div class="flex items-end gap-2">
                <button class="px-4 py-2 rounded-md bg-slate-900 text-white font-semibold">Filter</button>
                <a href="{{ route('clubs.index') }}" class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold text-slate-800">Reset</a>
            </div>
        </form>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($clubs as $club)
                <x-club-card :club="$club" />
            @empty
                <p class="col-span-full text-slate-600">No clubs match that search.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $clubs->links() }}</div>
    </div>
</x-app-layout>
