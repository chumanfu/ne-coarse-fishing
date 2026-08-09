<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NE Coarse Fishing') }}</title>
    <script>
        (function () {
            try {
                var key = 'necf-theme';
                var stored = localStorage.getItem(key);
                var dark = stored === 'dark' || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        @isset($siteAnnouncements)
            @foreach ($siteAnnouncements as $siteAnnouncement)
                <div class="border-b-2 {{ $siteAnnouncement->level === 'maintenance' ? 'bg-amber-100 border-amber-600 text-amber-950 dark:bg-amber-950 dark:border-amber-500 dark:text-amber-100' : ($siteAnnouncement->level === 'warning' ? 'bg-orange-100 border-orange-600 text-orange-950 dark:bg-orange-950 dark:border-orange-500 dark:text-orange-100' : 'bg-sky-100 border-sky-700 text-sky-950 dark:bg-sky-950 dark:border-sky-500 dark:text-sky-100') }}">
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
            <header class="bg-white border-b-2 border-slate-300 dark:bg-slate-900 dark:border-slate-700">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        @if (session('status'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-lg bg-emerald-50 border-2 border-emerald-600 text-emerald-950 px-4 py-3 text-sm font-semibold dark:bg-emerald-950 dark:border-emerald-500 dark:text-emerald-100">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <main class="flex-1">
            {{ $slot }}
        </main>

        @include('layouts.partials.footer')
    </div>

    <x-photo-lightbox />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
    {{ $scripts ?? '' }}
</body>
</html>
