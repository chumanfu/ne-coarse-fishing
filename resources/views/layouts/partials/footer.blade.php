<footer class="bg-slate-900 text-slate-300 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between mb-8">
            <div>
                <p class="font-semibold text-white tracking-wide text-base">{{ config('app.name') }}</p>
                <p class="text-sm mt-1 text-slate-400">Venue guides, interactive maps &amp; catch reports across the North East.</p>
            </div>
        </div>

        <nav aria-label="Site map" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            <div>
                <p class="font-bold uppercase tracking-wide text-xs text-slate-400 mb-3">Explore</p>
                <ul class="space-y-2">
                    <li><a href="{{ route('home') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Home</a></li>
                    <li><a href="{{ route('venues.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Venues</a></li>
                    <li><a href="{{ route('map.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Map</a></li>
                    <li><a href="{{ route('clubs.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Clubs</a></li>
                    <li><a href="{{ route('tackle-shops.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Tackle shops</a></li>
                    <li><a href="{{ route('tackle-reviews.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Tackle reviews</a></li>
                    <li><a href="{{ route('activity.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Activity</a></li>
                </ul>
            </div>

            <div>
                <p class="font-bold uppercase tracking-wide text-xs text-slate-400 mb-3">Community</p>
                <ul class="space-y-2">
                    <li><a href="{{ route('about') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">About</a></li>
                    <li><a href="{{ route('refer') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Refer a friend</a></li>
                    <li><a href="{{ route('contact.create') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Contact us</a></li>
                    <li><a href="{{ route('code-of-conduct') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Code of conduct &amp; privacy</a></li>
                </ul>
            </div>

            <div>
                <p class="font-bold uppercase tracking-wide text-xs text-slate-400 mb-3">Your account</p>
                <ul class="space-y-2">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Dashboard</a></li>
                        <li><a href="{{ route('sessions.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">My sessions</a></li>
                        <li><a href="{{ route('venues.favourites') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Favourites</a></li>
                        <li><a href="{{ route('messages.index') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Messages</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Profile &amp; GDPR export</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Log in</a></li>
                        <li><a href="{{ route('register') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Register</a></li>
                        <li><a href="{{ route('code-of-conduct') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">GDPR &amp; your data</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <p class="font-bold uppercase tracking-wide text-xs text-slate-400 mb-3">Help</p>
                <ul class="space-y-2">
                    <li><a href="{{ route('contact.create') }}" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Report a problem</a></li>
                    <li><a href="{{ route('code-of-conduct') }}#community-standards" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Report abuse</a></li>
                    <li><a href="{{ route('code-of-conduct') }}#gdpr-rights" class="font-semibold text-white hover:text-sky-200 underline-offset-2 hover:underline">Privacy &amp; data rights</a></li>
                </ul>
            </div>
        </nav>

        <p class="mt-10 pt-6 border-t border-slate-700 text-xs text-slate-500">
            &copy; {{ now()->year }} {{ config('app.name') }}. Built for North East coarse anglers.
        </p>
    </div>
</footer>
