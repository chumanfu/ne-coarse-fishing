@props([
    'image',
    'alt' => '',
    'eyebrow' => null,
    'title',
    'lead' => null,
    'imagePosition' => 'center 35%',
])

<section {{ $attributes->merge(['class' => 'page-hero']) }} aria-label="{{ $title }}">
    <div class="page-hero__media" aria-hidden="true">
        <img
            src="{{ $image }}"
            alt="{{ $alt }}"
            class="page-hero__image"
            style="object-position: {{ $imagePosition }};"
            fetchpriority="high"
        >
        <div class="page-hero__shade"></div>
    </div>

    <div class="page-hero__content">
        @if ($eyebrow)
            <p class="page-hero__eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="page-hero__title">{{ $title }}</h1>
        @if ($lead)
            <p class="page-hero__lead">{{ $lead }}</p>
        @endif
        @isset($actions)
            <div class="page-hero__actions">
                {{ $actions }}
            </div>
        @endisset
        @if ($slot->isNotEmpty())
            <div class="page-hero__extra">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
