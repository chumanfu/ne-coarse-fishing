<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Favourite venues</h1>
        <p class="text-slate-600 mt-1">Waters you’ve starred for quick access.</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('status'))
            <p class="mb-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</p>
        @endif

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($venues as $venue)
                <x-venue-card :venue="$venue" :is-favourited="true" />
            @empty
                <p class="text-slate-600 sm:col-span-2 lg:col-span-3">
                    No favourites yet.
                    <a href="{{ route('venues.index') }}" class="font-semibold text-sky-800 hover:underline">Browse venues</a>
                    and tap the star to save one.
                </p>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $venues->links() }}
        </div>
    </div>
</x-app-layout>
