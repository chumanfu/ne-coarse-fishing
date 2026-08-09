@props([
    'compact' => false,
])

<button
    type="button"
    data-theme-toggle
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-lg border transition',
        $compact
            ? 'h-9 w-9 border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800'
            : 'gap-2 px-3.5 py-2 text-sm font-semibold border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800',
    ]) }}
    @click="$store.theme.toggle()"
    :aria-label="$store.theme.dark ? 'Switch to day mode' : 'Switch to night mode'"
    :title="$store.theme.dark ? 'Day mode' : 'Night mode'"
>
    {{-- Sun (shown in night mode → click for day) --}}
    <svg x-show="$store.theme.dark" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true">
        <path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM10 7a3 3 0 100 6 3 3 0 000-6zM15.657 5.404a.75.75 0 10-1.06-1.06l-1.061 1.06a.75.75 0 001.06 1.06l1.06-1.06zM6.464 14.596a.75.75 0 10-1.06-1.06l-1.06 1.06a.75.75 0 001.06 1.06l1.06-1.06zM18 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0118 10zM5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 015 10zM14.596 14.657a.75.75 0 001.06-1.06l-1.06-1.061a.75.75 0 10-1.06 1.06l1.06 1.06zM5.404 6.464a.75.75 0 001.06-1.06l-1.06-1.06a.75.75 0 10-1.061 1.06l1.06 1.06z" />
    </svg>
    {{-- Moon (shown in day mode → click for night) --}}
    <svg x-show="! $store.theme.dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true">
        <path fill-rule="evenodd" d="M7.455 2.104a.75.75 0 01.162.77 7.003 7.003 0 009.522 9.522.75.75 0 011.055.887A8.5 8.5 0 116.568 1.95a.75.75 0 01.887.154z" clip-rule="evenodd" />
    </svg>
    @unless ($compact)
        <span class="hidden xl:inline" x-text="$store.theme.dark ? 'Day' : 'Night'"></span>
    @endunless
</button>
