<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tackle shops</h1>
            <p class="text-slate-600 mt-1">Independent North East shops and the big online retailers anglers use every week.</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="GET" class="bg-white border-2 border-slate-300 rounded-xl p-4 mb-6 grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-800 mb-1">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Shop name or town" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-1">Type</label>
                <select name="type" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Any</option>
                    @foreach (\App\Models\TackleShop::LOCATION_TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="px-4 py-2 rounded-md bg-slate-900 text-white font-semibold">Filter</button>
                <a href="{{ route('tackle-shops.index') }}" class="px-4 py-2 rounded-md border-2 border-slate-400 font-semibold text-slate-800">Reset</a>
            </div>
        </form>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($shops as $shop)
                <x-tackle-shop-card :shop="$shop" />
            @empty
                <p class="col-span-full text-slate-600">No tackle shops match those filters.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $shops->links() }}</div>
    </div>
</x-app-layout>
