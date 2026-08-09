<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased dark:text-slate-100">
        <div class="min-h-screen flex flex-col">
            <div class="flex-1 flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-100 dark:bg-slate-950">
                <div class="w-full sm:max-w-md px-6 flex items-center justify-between gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-sky-800 text-white font-bold text-sm tracking-tight">NE</span>
                        <span class="font-bold text-slate-900 tracking-tight text-[15px] dark:text-slate-100">{{ config('app.name', 'NE Coarse Fishing') }}</span>
                    </a>
                    <x-theme-toggle compact />
                </div>

                <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg dark:bg-slate-900 dark:ring-1 dark:ring-slate-700">
                    {{ $slot }}
                </div>
            </div>

            @include('layouts.partials.footer')
        </div>
    </body>
</html>
