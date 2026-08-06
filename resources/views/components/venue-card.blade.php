@props([
    'venue',
    'isFavourited' => false,
])

@php
    $favourited = $isFavourited || (auth()->check() && $venue->isFavouritedBy(auth()->user()));
@endphp

<div {{ $attributes->merge(['class' => 'relative bg-white border-2 border-slate-300 rounded-xl overflow-hidden hover:border-sky-700 transition']) }}>
    <a href="{{ route('venues.show', $venue) }}" class="block">
        @if ($venue->relationLoaded('photos') && $venue->photos->isNotEmpty())
            <img src="{{ $venue->photos->first()->url() }}" alt="" class="w-full h-36 object-cover border-b-2 border-slate-200">
        @endif
        <div class="p-5">
            <div class="flex items-start justify-between gap-3 pe-10">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">{{ $venue->name }}</h3>
                    @if ($venue->address)
                        <p class="text-sm text-slate-600 mt-1">{{ $venue->address }}</p>
                    @endif
                </div>
                @if ($venue->manager_verified)
                    <span class="shrink-0 text-xs font-bold uppercase tracking-wide bg-emerald-100 text-emerald-900 border border-emerald-600 px-2 py-1 rounded">Verified</span>
                @endif
            </div>
            <p class="text-sm text-slate-700 mt-3 line-clamp-3">{{ $venue->overview ?: 'Community-submitted venue awaiting a full write-up.' }}</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                <span class="bg-slate-100 border border-slate-300 px-2 py-1 rounded">{{ $venue->ticketTypeLabel() }}</span>
                @if ($venue->is_complex)
                    <span class="bg-amber-50 border border-amber-500 text-amber-950 px-2 py-1 rounded">Complex</span>
                @endif
                @foreach ($venue->allSpecies()->take(4) as $species)
                    <span class="bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">{{ $species->name }}</span>
                @endforeach
            </div>
        </div>
    </a>

    @auth
        <form method="POST"
              action="{{ $favourited ? route('venues.favourite.destroy', $venue) : route('venues.favourite.store', $venue) }}"
              class="absolute top-3 end-3 z-10">
            @csrf
            @if ($favourited)
                @method('DELETE')
            @endif
            <button type="submit"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border-2 bg-white/95 shadow-sm {{ $favourited ? 'border-amber-500 text-amber-700' : 'border-slate-400 text-slate-600' }} hover:border-amber-600 hover:text-amber-800"
                    title="{{ $favourited ? 'Remove from favourites' : 'Add to favourites' }}"
                    aria-label="{{ $favourited ? 'Remove from favourites' : 'Add to favourites' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="{{ $favourited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
            </button>
        </form>
    @endauth
</div>
