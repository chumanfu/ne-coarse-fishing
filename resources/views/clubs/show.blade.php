<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-sky-800 uppercase tracking-wide mb-1">Angling club</p>
                <h1 class="text-2xl font-bold text-slate-900">{{ $club->name }}</h1>
                @if ($club->town)
                    <p class="text-slate-600 mt-1">{{ $club->town }}</p>
                @endif
            </div>
            @if ($club->url)
                <a href="{{ $club->url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 rounded-md bg-sky-700 text-white font-semibold hover:bg-sky-800">
                    Visit website
                </a>
            @endif
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
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
            </dl>
        </div>

        <a href="{{ route('clubs.index') }}" class="inline-flex text-sky-800 font-semibold hover:underline">&larr; All clubs</a>
    </div>
</x-app-layout>
