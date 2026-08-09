@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-sky-500 text-start text-base font-medium text-sky-800 bg-sky-50 focus:outline-none transition duration-150 ease-in-out dark:border-sky-400 dark:text-sky-100 dark:bg-sky-950/60'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 hover:border-slate-300 focus:outline-none transition duration-150 ease-in-out dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800 dark:hover:border-slate-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
