@props(['shop'])

@php
    /** @var \App\Models\TackleShop $shop */
@endphp

<article {{ $attributes->class('site-card p-5 flex flex-col') }}>
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if ($shop->logoUrl())
                <div class="tackle-shop-logo rounded-lg border border-[#d6cfc2] bg-bank-soft p-2">
                    <button
                        type="button"
                        class="block cursor-zoom-in"
                        @click="$store.photoLightbox.open(@js([['url' => $shop->logoUrl(), 'alt' => $shop->name.' logo']]), 0, @js($shop->name.' logo'))"
                    >
                        <img
                            src="{{ $shop->logoUrl() }}"
                            alt="{{ $shop->name }} logo"
                            width="64"
                            height="64"
                            class="tackle-shop-logo__img"
                            loading="lazy"
                        >
                    </button>
                </div>
            @endif
            <h3 class="font-display text-lg text-ink min-w-0">
                <a href="{{ route('tackle-shops.show', $shop) }}" class="hover:text-moss">{{ $shop->name }}</a>
            </h3>
        </div>
        <span class="shrink-0 text-xs font-semibold tracking-wide bg-bank-soft border border-[#d6cfc2] text-ink px-2 py-1 rounded-md">
            {{ $shop->locationTypeLabel() }}
        </span>
    </div>

    @if ($shop->town)
        <p class="text-sm font-semibold text-ink-muted mt-2">{{ $shop->town }}</p>
    @endif

    <p class="text-sm text-ink-muted mt-2 line-clamp-3 flex-1">{{ $shop->overview ?: 'No description yet.' }}</p>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <a href="{{ $shop->url }}"
           target="_blank"
           rel="noopener noreferrer"
           class="site-btn site-btn--primary text-sm">
            Visit website
        </a>
        @if ($shop->websiteHost())
            <span class="text-xs font-semibold text-ink-soft">{{ $shop->websiteHost() }}</span>
        @endif
    </div>
</article>
