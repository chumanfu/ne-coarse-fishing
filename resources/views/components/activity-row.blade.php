@props(['activity', 'compact' => false])

@if ($compact)
    <a
        href="{{ url($activity->url) }}"
        {{ $attributes->merge(['class' => 'flex flex-col gap-1.5 bg-white border-2 border-slate-200 rounded-lg px-3 py-2.5 hover:border-sky-700 transition dark:bg-slate-900 dark:border-slate-700 dark:hover:border-sky-500']) }}
    >
        <div class="flex items-center justify-between gap-2">
            <span class="shrink-0 text-xs font-bold uppercase tracking-wide bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded w-fit">
                {{ $activity->typeLabel() }}
            </span>
            <time class="shrink-0 text-xs font-semibold text-slate-500" datetime="{{ $activity->created_at->toIso8601String() }}">
                {{ $activity->created_at->diffForHumans(short: true) }}
            </time>
        </div>
        <div class="min-w-0">
            <p class="font-semibold text-sm leading-snug text-slate-900">{{ $activity->title }}</p>
            @if ($activity->summary)
                <p class="text-xs text-slate-600 truncate mt-0.5">{{ $activity->summary }}</p>
            @endif
        </div>
    </a>
@else
    <a href="{{ url($activity->url) }}" {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 hover:border-sky-700 transition']) }}>
        <span class="shrink-0 text-xs font-bold uppercase tracking-wide bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded w-fit">
            {{ $activity->typeLabel() }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-slate-900">{{ $activity->title }}</p>
            @if ($activity->summary)
                <p class="text-sm text-slate-600 truncate">{{ $activity->summary }}</p>
            @endif
        </div>
        <time class="shrink-0 text-xs font-semibold text-slate-500" datetime="{{ $activity->created_at->toIso8601String() }}">
            {{ $activity->created_at->diffForHumans() }}
        </time>
    </a>
@endif
