@props([
    'photos' => [],
    'alt' => 'Photo',
    'label' => null,
    'thumbClass' => 'rounded-lg object-cover h-40 w-full border border-slate-300 hover:border-sky-700',
    'gridClass' => 'grid grid-cols-2 sm:grid-cols-3 gap-3',
    'buttonClass' => 'relative block w-full text-left',
])

@php
    $items = collect($photos)
        ->map(function ($photo) use ($alt) {
            if (is_string($photo)) {
                return ['url' => $photo, 'alt' => $alt];
            }

            if (is_array($photo)) {
                return [
                    'url' => $photo['url'] ?? '',
                    'alt' => $photo['alt'] ?? $alt,
                ];
            }

            if (is_object($photo) && method_exists($photo, 'url')) {
                return [
                    'url' => $photo->url(),
                    'alt' => $alt,
                ];
            }

            return null;
        })
        ->filter(fn ($photo) => filled($photo['url'] ?? null))
        ->values()
        ->all();

    $dialogLabel = $label ?? $alt;
@endphp

@if (count($items) > 0)
    <div x-data {{ $attributes->merge(['class' => $gridClass]) }}>
        @foreach ($items as $index => $photo)
            <button
                type="button"
                class="{{ $buttonClass }}"
                @click="$store.photoLightbox.open(@js($items), {{ $index }}, @js($dialogLabel))"
            >
                <img
                    src="{{ $photo['url'] }}"
                    alt="{{ $photo['alt'] }}"
                    class="{{ $thumbClass }}"
                >
            </button>
        @endforeach
    </div>
@endif
