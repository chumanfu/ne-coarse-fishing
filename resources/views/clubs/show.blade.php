<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                @if ($club->logoUrl())
                    <div class="directory-logo directory-logo--lg rounded-xl border-2 border-slate-300 bg-white p-2">
                        <button
                            type="button"
                            class="block cursor-zoom-in"
                            @click="$store.photoLightbox.open(@js([['url' => $club->logoUrl(), 'alt' => $club->name.' logo']]), 0, @js($club->name.' logo'))"
                        >
                            <img
                                src="{{ $club->logoUrl() }}"
                                alt="{{ $club->name }} logo"
                                width="72"
                                height="72"
                                class="directory-logo__img"
                            >
                        </button>
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-sky-800 uppercase tracking-wide mb-1">Angling club</p>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $club->name }}</h1>
                    @if ($club->town)
                        <p class="text-slate-600 mt-1">{{ $club->town }}</p>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
            @if ($club->url)
                <a href="{{ $club->url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 rounded-md bg-sky-700 text-white font-semibold hover:bg-sky-800">
                    Visit website
                </a>
            @endif
            @if ($club->facebook_url)
                <a href="{{ $club->facebook_url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold hover:bg-sky-50">
                    Facebook
                </a>
            @endif
            @auth
                @can('manage', $club)
                    <a href="{{ route('clubs.edit', $club) }}" class="inline-flex items-center px-4 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold hover:bg-sky-50">Edit club</a>
                @elsecan('suggestEdit', $club)
                    <a href="{{ route('clubs.suggest-edit', $club) }}" class="inline-flex items-center px-4 py-2 rounded-md border-2 border-amber-600 text-amber-950 font-semibold hover:bg-amber-50">Suggest an edit</a>
                @endcan
                @can('claim', $club)
                    <form method="POST" action="{{ route('clubs.claim', $club) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md border-2 border-amber-600 text-amber-950 font-semibold hover:bg-amber-50" onclick="return confirm('Claim management of this club?')">Claim ownership</button>
                    </form>
                @endcan
            @endauth
            </div>
        </div>
        @if ($club->manager_verified && $club->manager)
            <p class="mt-3 text-sm text-emerald-800 font-semibold">Managed by {{ $club->manager->name }}</p>
        @endif
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="bg-white border-2 border-slate-300 rounded-xl p-6">
            @if ($club->websiteHost())
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wide bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">
                        {{ $club->websiteHost() }}
                    </span>
                </div>
            @endif

            <p class="text-slate-700 leading-relaxed">{{ $club->overview ?: 'No description yet.' }}</p>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2 text-sm">
                @if ($club->address)
                    <div>
                        <dt class="font-semibold text-slate-900">Address</dt>
                        <dd class="text-slate-600 mt-1">{{ $club->address }}</dd>
                    </div>
                @endif
                @if ($club->phone)
                    <div>
                        <dt class="font-semibold text-slate-900">Phone</dt>
                        <dd class="text-slate-600 mt-1">
                            <a href="tel:{{ preg_replace('/\s+/', '', $club->phone) }}" class="hover:text-sky-800">{{ $club->phone }}</a>
                        </dd>
                    </div>
                @endif
                @if ($club->url)
                    <div class="sm:col-span-2">
                        <dt class="font-semibold text-slate-900">Website</dt>
                        <dd class="text-slate-600 mt-1 break-all">
                            <a href="{{ $club->url }}" target="_blank" rel="noopener noreferrer" class="text-sky-800 font-semibold hover:underline">
                                {{ $club->url }}
                            </a>
                        </dd>
                    </div>
                @endif
                @if ($club->facebook_url)
                    <div class="sm:col-span-2">
                        <dt class="font-semibold text-slate-900">Facebook</dt>
                        <dd class="text-slate-600 mt-1 break-all">
                            <a href="{{ $club->facebook_url }}" target="_blank" rel="noopener noreferrer" class="text-sky-800 font-semibold hover:underline">
                                {{ $club->facebook_url }}
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($club->venues->isNotEmpty())
            <div class="bg-white border-2 border-slate-300 rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-1">Owned waters</h2>
                <p class="text-sm text-slate-600 mb-4">Venues owned or managed by this club.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($club->venues as $venue)
                        <x-venue-card :venue="$venue" />
                    @endforeach
                </div>
            </div>
        @endif

        <a href="{{ route('clubs.index') }}" class="inline-flex text-sky-800 font-semibold hover:underline">&larr; All clubs</a>
    </div>
</x-app-layout>
