@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-moss text-sm font-semibold leading-5 text-ink focus:outline-none focus:border-moss-dark transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-ink-muted hover:text-ink hover:border-[#c4bbad] focus:outline-none focus:text-ink focus:border-[#c4bbad] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
