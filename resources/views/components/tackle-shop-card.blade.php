@props(['shop'])

@php
    /** @var \App\Models\TackleShop $shop */
@endphp

<article {{ $attributes->class('bg-white border-2 border-slate-300 rounded-xl p-5 hover:border-sky-700 transition flex flex-col') }}>
    <div class="flex items-start justify-between gap-3">
        <h3 class="font-bold text-lg text-slate-900">
            <a href="{{ route('tackle-shops.show', $shop) }}" class="hover:text-sky-800">{{ $shop->name }}</a>
        </h3>
        <span class="shrink-0 text-xs font-bold uppercase tracking-wide bg-slate-100 border border-slate-300 text-slate-800 px-2 py-1 rounded">
            {{ $shop->locationTypeLabel() }}
        </span>
    </div>

    @if ($shop->town)
        <p class="text-sm font-semibold text-slate-700 mt-2">{{ $shop->town }}</p>
    @endif

    <p class="text-sm text-slate-600 mt-2 line-clamp-3 flex-1">{{ $shop->overview ?: 'No description yet.' }}</p>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <a href="{{ $shop->url }}"
           target="_blank"
           rel="noopener noreferrer"
           class="inline-flex items-center px-3 py-2 rounded-md bg-sky-700 text-white text-sm font-semibold hover:bg-sky-800">
            Visit website
        </a>
        @if ($shop->websiteHost())
            <span class="text-xs font-semibold text-slate-500">{{ $shop->websiteHost() }}</span>
        @endif
    </div>
</article>
