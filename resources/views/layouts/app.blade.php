<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NE Coarse Fishing') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-900">
    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        @isset($siteAnnouncements)
            @foreach ($siteAnnouncements as $siteAnnouncement)
                <div class="border-b-2 {{ $siteAnnouncement->level === 'maintenance' ? 'bg-amber-100 border-amber-600 text-amber-950' : ($siteAnnouncement->level === 'warning' ? 'bg-orange-100 border-orange-600 text-orange-950' : 'bg-sky-100 border-sky-700 text-sky-950') }}">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                        <p class="text-xs font-bold uppercase tracking-wide">{{ $siteAnnouncement->levelLabel() }} · site notice</p>
                        <p class="font-bold mt-0.5">{{ $siteAnnouncement->title }}</p>
                        <p class="text-sm mt-1 whitespace-pre-line">{{ $siteAnnouncement->body }}</p>
                        <p class="text-xs mt-2 opacity-80">Visible until {{ $siteAnnouncement->ends_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                    </div>
                </div>
            @endforeach
        @endisset

        @isset($header)
            <header class="bg-white border-b-2 border-slate-300">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        @if (session('status'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-lg bg-emerald-50 border-2 border-emerald-600 text-emerald-950 px-4 py-3 text-sm font-semibold">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="bg-slate-900 text-slate-300 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-sm flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <p class="font-semibold text-white tracking-wide">{{ config('app.name') }}</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6">
                    <p>Venue guides, interactive maps &amp; catch reports across the North East.</p>
                    <a href="{{ route('about') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">About</a>
                    <a href="{{ route('refer') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Refer a friend</a>
                    <a href="{{ route('tackle-reviews.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Tackle reviews</a>
                    <a href="{{ route('contact.create') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Contact us</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
    {{ $scripts ?? '' }}
</body>
</html>
