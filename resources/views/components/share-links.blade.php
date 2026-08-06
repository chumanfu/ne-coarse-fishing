@props([
    'url',
    'title' => config('app.name'),
    'text' => null,
    'label' => 'Share',
])

@php
    $shareUrl = $url;
    $shareText = $text ?: $title;
    $encodedUrl = rawurlencode($shareUrl);
    $encodedText = rawurlencode($shareText);
    $whatsapp = 'https://wa.me/?text='.rawurlencode($shareText.' '.$shareUrl);
    $facebook = 'https://www.facebook.com/sharer/sharer.php?u='.$encodedUrl;
    $email = 'mailto:?subject='.rawurlencode($title).'&body='.rawurlencode($shareText."\n\n".$shareUrl);
@endphp

<div
    {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}
    x-data="{ copied: false, async copyForInstagram() {
        try {
            await navigator.clipboard.writeText(@js($shareUrl));
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        } catch (e) {
            window.prompt('Copy this link for Instagram:', @js($shareUrl));
        }
    }}"
>
    <span class="text-sm font-semibold text-slate-700 me-1">{{ $label }}</span>
    <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3 py-1.5 rounded-md border-2 border-emerald-600 text-emerald-900 text-sm font-semibold hover:bg-emerald-50">WhatsApp</a>
    <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3 py-1.5 rounded-md border-2 border-sky-700 text-sky-900 text-sm font-semibold hover:bg-sky-50">Facebook</a>
    <button type="button" @click="copyForInstagram()" class="inline-flex items-center px-3 py-1.5 rounded-md border-2 border-fuchsia-600 text-fuchsia-950 text-sm font-semibold hover:bg-fuchsia-50">
        <span x-text="copied ? 'Link copied' : 'Instagram'"></span>
    </button>
    <a href="{{ $email }}" class="inline-flex items-center px-3 py-1.5 rounded-md border-2 border-slate-600 text-slate-800 text-sm font-semibold hover:bg-slate-50">Email</a>
</div>
