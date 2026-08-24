@props(['activity', 'compact' => false])

@if ($compact)
    <a
        href="{{ url($activity->url) }}"
        {{ $attributes->merge(['class' => 'flex flex-col gap-1.5 bg-paper-bright border border-[#d6cfc2] rounded-xl px-3 py-2.5 hover:border-moss-light hover:shadow-soft transition']) }}
    >
        <div class="flex items-center justify-between gap-2">
            <span class="shrink-0 text-xs font-semibold tracking-wide bg-moss-soft border border-moss/25 text-moss-dark px-2 py-1 rounded-md w-fit">
                {{ $activity->typeLabel() }}
            </span>
            <time class="shrink-0 text-xs font-semibold text-ink-soft" datetime="{{ $activity->created_at->toIso8601String() }}">
                {{ $activity->created_at->diffForHumans(short: true) }}
            </time>
        </div>
        <div class="min-w-0">
            <p class="font-semibold text-sm leading-snug text-ink">{{ $activity->title }}</p>
            @if ($activity->summary)
                <p class="text-xs text-ink-muted truncate mt-0.5">{{ $activity->summary }}</p>
            @endif
        </div>
    </a>
@else
    <a href="{{ url($activity->url) }}" {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 bg-paper-bright border border-[#d6cfc2] rounded-xl px-4 py-3 hover:border-moss-light hover:shadow-soft transition']) }}>
        <span class="shrink-0 text-xs font-semibold tracking-wide bg-moss-soft border border-moss/25 text-moss-dark px-2 py-1 rounded-md w-fit">
            {{ $activity->typeLabel() }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-ink">{{ $activity->title }}</p>
            @if ($activity->summary)
                <p class="text-sm text-ink-muted truncate">{{ $activity->summary }}</p>
            @endif
        </div>
        <time class="shrink-0 text-xs font-semibold text-ink-soft" datetime="{{ $activity->created_at->toIso8601String() }}">
            {{ $activity->created_at->diffForHumans() }}
        </time>
    </a>
@endif
