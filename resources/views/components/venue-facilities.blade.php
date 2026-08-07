@props(['venue'])

@php
    /** @var \App\Models\Venue $venue */
    $available = collect($venue->facilities ?? []);
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    <p class="text-sm font-semibold text-slate-800 mb-2">Facilities</p>
    <ul class="grid grid-cols-2 gap-2">
        @foreach (\App\Models\Venue::FACILITIES as $key => $label)
            @php
                $isAvailable = $available->contains($key);
            @endphp
            <li @class([
                'flex items-center gap-2 rounded-lg border px-2.5 py-2 text-xs font-semibold',
                'border-sky-200 bg-sky-50 text-sky-950' => $isAvailable,
                'border-slate-200 bg-slate-50 text-slate-400' => ! $isAvailable,
            ])>
                <span @class([
                    'inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md border',
                    'border-sky-300 bg-white text-sky-800' => $isAvailable,
                    'border-slate-200 bg-slate-100 text-slate-400' => ! $isAvailable,
                ]) aria-hidden="true">
                    @include('components.partials.facility-icon', ['facility' => $key])
                </span>
                <span class="leading-snug">{{ $label }}</span>
            </li>
        @endforeach
    </ul>
</div>
