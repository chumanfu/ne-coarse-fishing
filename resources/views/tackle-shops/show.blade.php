<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                @if ($shop->logoUrl())
                    <div class="tackle-shop-logo tackle-shop-logo--lg rounded-xl border-2 border-slate-300 bg-white p-2">
                        <img
                            src="{{ $shop->logoUrl() }}"
                            alt="{{ $shop->name }} logo"
                            width="72"
                            height="72"
                            class="tackle-shop-logo__img"
                        >
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-sky-800 uppercase tracking-wide mb-1">Tackle shop</p>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $shop->name }}</h1>
                    @if ($shop->town)
                        <p class="text-slate-600 mt-1">{{ $shop->town }}</p>
                    @endif
                </div>
            </div>
            <a href="{{ $shop->url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center px-4 py-2 rounded-md bg-sky-700 text-white font-semibold hover:bg-sky-800">
                Visit website
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="bg-white border-2 border-slate-300 rounded-xl p-6">
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-xs font-bold uppercase tracking-wide bg-slate-100 border border-slate-300 px-2 py-1 rounded">
                    {{ $shop->locationTypeLabel() }}
                </span>
                @if ($shop->websiteHost())
                    <span class="text-xs font-bold uppercase tracking-wide bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">
                        {{ $shop->websiteHost() }}
                    </span>
                @endif
            </div>

            <p class="text-slate-700 leading-relaxed">{{ $shop->overview ?: 'No description yet.' }}</p>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2 text-sm">
                @if ($shop->address)
                    <div>
                        <dt class="font-semibold text-slate-900">Address</dt>
                        <dd class="text-slate-600 mt-1">{{ $shop->address }}</dd>
                    </div>
                @endif
                @if ($shop->phone)
                    <div>
                        <dt class="font-semibold text-slate-900">Phone</dt>
                        <dd class="text-slate-600 mt-1">
                            <a href="tel:{{ preg_replace('/\s+/', '', $shop->phone) }}" class="hover:text-sky-800">{{ $shop->phone }}</a>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="font-semibold text-slate-900">Website</dt>
                    <dd class="text-slate-600 mt-1 break-all">
                        <a href="{{ $shop->url }}" target="_blank" rel="noopener noreferrer" class="text-sky-800 font-semibold hover:underline">
                            {{ $shop->url }}
                        </a>
                    </dd>
                </div>
            </dl>
        </div>

        <a href="{{ route('tackle-shops.index') }}" class="inline-flex text-sky-800 font-semibold hover:underline">&larr; All tackle shops</a>
    </div>
</x-app-layout>
