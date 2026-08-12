@props([
    'items' => [],
])

@php
    $crumbs = collect($items)
        ->filter(fn ($item) => filled($item['label'] ?? null))
        ->values();
@endphp

@if ($crumbs->isNotEmpty())
    <nav {{ $attributes->merge(['aria-label' => 'Breadcrumb', 'class' => 'mb-3']) }}>
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
            @foreach ($crumbs as $item)
                @php
                    $label = $item['label'];
                    $url = $item['url'] ?? null;
                    $isLast = $loop->last;
                @endphp
                <li class="inline-flex items-center gap-2 min-w-0">
                    @unless ($loop->first)
                        <span class="text-slate-400 dark:text-slate-500 select-none" aria-hidden="true">/</span>
                    @endunless
                    @if ($url && ! $isLast)
                        <a href="{{ $url }}" class="font-semibold text-sky-800 hover:underline truncate dark:text-sky-300">{{ $label }}</a>
                    @else
                        <span @if ($isLast) aria-current="page" @endif class="font-semibold text-slate-700 truncate dark:text-slate-200">{{ $label }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
