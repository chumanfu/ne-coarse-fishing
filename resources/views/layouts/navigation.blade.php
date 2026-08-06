<nav x-data="{ open: false }" class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 gap-4">
            <div class="flex items-center gap-6 min-w-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-sky-800 text-white font-bold text-sm tracking-tight">NE</span>
                    <span class="font-bold text-slate-900 tracking-tight hidden md:inline text-[15px] whitespace-nowrap">NE Coarse Fishing</span>
                </a>

                <div class="hidden space-x-5 lg:flex">
                    <x-nav-link :href="route('venues.index')" :active="request()->routeIs('venues.*')">Venues</x-nav-link>
                    <x-nav-link :href="route('clubs.index')" :active="request()->routeIs('clubs.*')">Clubs</x-nav-link>
                    <x-nav-link :href="route('tackle-shops.index')" :active="request()->routeIs('tackle-shops.*')">Tackle shops</x-nav-link>
                    <x-nav-link :href="route('tackle-reviews.index')" :active="request()->routeIs('tackle-reviews.*')">Reviews</x-nav-link>
                    <x-nav-link :href="route('map.index')" :active="request()->routeIs('map.*')">Map</x-nav-link>
                    <x-nav-link :href="route('about')" :active="request()->routeIs('about')">About</x-nav-link>
                    <x-nav-link :href="route('contact.create')" :active="request()->routeIs('contact.*')">Contact</x-nav-link>
                </div>
            </div>

            <div class="hidden lg:flex lg:items-center lg:gap-3 shrink-0">
                @auth
                    <a href="{{ route('venues.create') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-semibold rounded-lg bg-sky-800 text-white hover:bg-sky-900 whitespace-nowrap">
                        Add Venue
                    </a>
                    <a href="{{ route('sessions.create') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-semibold rounded-lg border border-slate-300 text-slate-800 hover:bg-slate-50 whitespace-nowrap">
                        Log Session
                    </a>
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center max-w-[11rem] px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:text-slate-900">
                                <span class="truncate">{{ Auth::user()->name }}</span>
                                <span class="ms-1 shrink-0">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('dashboard')">Dashboard</x-dropdown-link>
                            <x-dropdown-link :href="route('sessions.index')">My Sessions</x-dropdown-link>
                            <x-dropdown-link :href="route('venues.favourites')">Favourites</x-dropdown-link>
                            <x-dropdown-link :href="route('refer')">Refer a friend</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                            @role('super_admin')
                                <x-dropdown-link href="/admin">Admin Panel</x-dropdown-link>
                            @endrole
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-semibold rounded-lg border border-sky-800 text-sky-900 hover:bg-sky-50">Log in</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-semibold rounded-lg bg-sky-800 text-white hover:bg-sky-900">Register</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-600 hover:bg-slate-100" aria-label="Toggle navigation">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden border-t border-slate-200">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('venues.index')" :active="request()->routeIs('venues.*')">Venues</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('clubs.index')" :active="request()->routeIs('clubs.*')">Clubs</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tackle-shops.index')" :active="request()->routeIs('tackle-shops.*')">Tackle shops</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tackle-reviews.index')" :active="request()->routeIs('tackle-reviews.*')">Reviews</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('map.index')" :active="request()->routeIs('map.*')">Map</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('about')" :active="request()->routeIs('about')">About</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('contact.create')" :active="request()->routeIs('contact.*')">Contact</x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sessions.index')" :active="request()->routeIs('sessions.*')">My Sessions</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('venues.favourites')" :active="request()->routeIs('venues.favourites')">Favourites</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('refer')" :active="request()->routeIs('refer')">Refer a friend</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('venues.create')">Add Venue</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sessions.create')">Log Session</x-responsive-nav-link>
            @endauth
        </div>
        <div class="pt-4 pb-3 border-t border-slate-200">
            @auth
                <div class="px-4 mb-2">
                    <div class="font-medium text-base text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-responsive-nav-link>
                </form>
            @else
                <x-responsive-nav-link :href="route('login')">Log in</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">Register</x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>
