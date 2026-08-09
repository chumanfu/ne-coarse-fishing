@props(['club'])

@php
    /** @var \App\Models\Club $club */
@endphp

<article {{ $attributes->class('bg-white border-2 border-slate-300 rounded-xl p-5 hover:border-sky-700 transition flex flex-col overflow-hidden dark:bg-slate-900 dark:border-slate-700 dark:hover:border-sky-500') }}>
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if ($club->logoUrl())
                <div class="directory-logo rounded-lg border border-slate-200 bg-slate-50 p-2">
                    <img
                        src="{{ $club->logoUrl() }}"
                        alt="{{ $club->name }} logo"
                        width="64"
                        height="64"
                        class="directory-logo__img"
                        loading="lazy"
                    >
                </div>
            @endif
            <h3 class="font-bold text-lg text-slate-900 min-w-0">
                <a href="{{ route('clubs.show', $club) }}" class="hover:text-sky-800">{{ $club->name }}</a>
            </h3>
        </div>
        @if ($club->town)
            <span class="shrink-0 text-xs font-bold uppercase tracking-wide bg-slate-100 border border-slate-300 text-slate-800 px-2 py-1 rounded">
                {{ $club->town }}
            </span>
        @endif
    </div>

    <p class="text-sm text-slate-600 mt-2 line-clamp-3 flex-1">{{ $club->overview ?: 'No description yet.' }}</p>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('clubs.show', $club) }}" class="inline-flex items-center px-3 py-2 rounded-md border-2 border-slate-800 text-slate-900 text-sm font-semibold hover:bg-slate-50">
            Club details
        </a>
        @if ($club->url)
            <a href="{{ $club->url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center px-3 py-2 rounded-md bg-sky-700 text-white text-sm font-semibold hover:bg-sky-800">
                Visit website
            </a>
            @if ($club->websiteHost())
                <span class="text-xs font-semibold text-slate-500">{{ $club->websiteHost() }}</span>
            @endif
        @endif
        @if ($club->facebook_url)
            <a href="{{ $club->facebook_url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 text-sm font-semibold hover:bg-sky-50">
                Facebook
            </a>
        @endif
    </div>
</article>
