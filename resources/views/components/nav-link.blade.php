@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-sky-700 text-sm font-semibold leading-5 text-slate-900 focus:outline-none focus:border-sky-800 transition duration-150 ease-in-out dark:text-slate-100 dark:border-sky-400'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-800 hover:border-slate-300 focus:outline-none focus:text-slate-800 focus:border-slate-300 transition duration-150 ease-in-out dark:text-slate-400 dark:hover:text-slate-100 dark:hover:border-slate-500';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
