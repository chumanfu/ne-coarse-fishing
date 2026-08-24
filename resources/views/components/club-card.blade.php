@props(['club'])

@php
    /** @var \App\Models\Club $club */
@endphp

<article {{ $attributes->class('site-card p-5 flex flex-col') }}>
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if ($club->logoUrl())
                <div class="directory-logo rounded-lg border border-[#d6cfc2] bg-moss-soft/50 p-2">
                    <button
                        type="button"
                        class="block cursor-zoom-in"
                        @click="$store.photoLightbox.open(@js([['url' => $club->logoUrl(), 'alt' => $club->name.' logo']]), 0, @js($club->name.' logo'))"
                    >
                        <img
                            src="{{ $club->logoUrl() }}"
                            alt="{{ $club->name }} logo"
                            width="64"
                            height="64"
                            class="directory-logo__img"
                            loading="lazy"
                        >
                    </button>
                </div>
            @endif
            <h3 class="font-display text-lg text-ink min-w-0">
                <a href="{{ route('clubs.show', $club) }}" class="hover:text-moss">{{ $club->name }}</a>
            </h3>
        </div>
        @if ($club->town)
            <span class="shrink-0 text-xs font-semibold tracking-wide bg-bank-soft border border-[#d6cfc2] text-ink px-2 py-1 rounded-md">
                {{ $club->town }}
            </span>
        @endif
    </div>

    <p class="text-sm text-ink-muted mt-2 line-clamp-3 flex-1">{{ $club->overview ?: 'No description yet.' }}</p>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('clubs.show', $club) }}" class="site-btn site-btn--ghost text-sm">
            Club details
        </a>
        @if ($club->url)
            <a href="{{ $club->url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="site-btn site-btn--primary text-sm">
                Visit website
            </a>
            @if ($club->websiteHost())
                <span class="text-xs font-semibold text-ink-soft">{{ $club->websiteHost() }}</span>
            @endif
        @endif
        @if ($club->facebook_url)
            <a href="{{ $club->facebook_url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="site-btn site-btn--ghost text-sm">
                Facebook
            </a>
        @endif
    </div>
</article>
