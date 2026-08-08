@props([
    'src',
    'alt' => 'Pond map',
    'maxHeightClass' => 'max-h-80',
])

{{--
  Shrink-wrap the image so peg % coordinates are relative to the visible bitmap,
  not a letterboxed full-width object-contain box.
--}}
<div {{ $attributes->class(['flex justify-center rounded-lg border-2 border-slate-400 overflow-hidden bg-slate-100']) }}>
    <div class="relative inline-block max-w-full">
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            class="pointer-events-none block max-w-full h-auto w-auto {{ $maxHeightClass }}"
        >
        {{ $slot }}
    </div>
</div>
