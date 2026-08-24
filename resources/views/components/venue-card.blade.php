@props([
    'venue',
    'isFavourited' => false,
])

@php
    $favourited = $isFavourited || (auth()->check() && $venue->isFavouritedBy(auth()->user()));
@endphp

<div {{ $attributes->merge(['class' => 'relative site-card']) }}>
    <a href="{{ route('venues.show', $venue) }}" class="block">
        @if ($venue->relationLoaded('photos') && $venue->photos->isNotEmpty())
            <img
                src="{{ $venue->photos->first()->url() }}"
                alt="{{ $venue->name }} photo"
                class="w-full h-36 object-cover border-b border-[#d6cfc2] cursor-zoom-in"
                role="button"
                tabindex="0"
                @click.prevent.stop="$store.photoLightbox.open(@js([['url' => $venue->photos->first()->url(), 'alt' => $venue->name.' photo']]), 0, @js($venue->name.' photo'))"
                @keydown.enter.prevent.stop="$store.photoLightbox.open(@js([['url' => $venue->photos->first()->url(), 'alt' => $venue->name.' photo']]), 0, @js($venue->name.' photo'))"
                @keydown.space.prevent.stop="$store.photoLightbox.open(@js([['url' => $venue->photos->first()->url(), 'alt' => $venue->name.' photo']]), 0, @js($venue->name.' photo'))"
            >
        @endif
        <div class="p-5">
            <div class="flex items-start justify-between gap-3 pe-10">
                <div>
                    <h3 class="font-display text-lg text-ink">{{ $venue->name }}</h3>
                    @if ($venue->address)
                        <p class="text-sm text-ink-muted mt-1">{{ $venue->address }}</p>
                    @endif
                </div>
                @if ($venue->manager_verified)
                    <span class="shrink-0 text-xs font-semibold tracking-wide bg-moss-soft text-moss-dark border border-moss/30 px-2 py-1 rounded-md">Verified</span>
                @endif
            </div>
            <p class="text-sm text-ink-muted mt-3 line-clamp-3">{{ $venue->overview ?: 'Community-submitted venue awaiting a full write-up.' }}</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                <span class="bg-bank-soft border border-[#d6cfc2] text-ink px-2 py-1 rounded-md">{{ $venue->ticketTypeLabel() }}</span>
                @if ($venue->is_complex)
                    <span class="bg-bank-soft border border-bank/30 text-bank px-2 py-1 rounded-md">Complex</span>
                @endif
                @if ($venue->relationLoaded('clubs') ? $venue->clubs->isNotEmpty() : (($venue->clubs_count ?? 0) > 0))
                    <span class="bg-water-soft border border-water/30 text-water-dark px-2 py-1 rounded-md">Club owned</span>
                @endif
                @foreach ($venue->allSpecies()->take(4) as $species)
                    <span class="bg-water-soft border border-water/30 text-water-dark px-2 py-1 rounded-md">{{ $species->name }}</span>
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
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border bg-paper-bright/95 shadow-soft {{ $favourited ? 'border-amber-500 text-amber-700' : 'border-[#c4bbad] text-ink-muted' }} hover:border-amber-600 hover:text-amber-800"
                    title="{{ $favourited ? 'Remove from favourites' : 'Add to favourites' }}"
                    aria-label="{{ $favourited ? 'Remove from favourites' : 'Add to favourites' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="{{ $favourited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
            </button>
        </form>
    @endauth
</div>
