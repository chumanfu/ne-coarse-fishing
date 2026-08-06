@props(['rating' => 0, 'max' => 5])

@php
    $rating = max(0, min((int) $max, (int) $rating));
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 text-amber-600', 'aria-label' => $rating.' out of '.$max.' stars']) }}>
    @for ($i = 1; $i <= $max; $i++)
        <span class="text-base leading-none" aria-hidden="true">{{ $i <= $rating ? '★' : '☆' }}</span>
    @endfor
    <span class="ms-1 text-sm font-semibold text-slate-700">{{ $rating }}/{{ $max }}</span>
</span>
