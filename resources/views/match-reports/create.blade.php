<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Publish match report</h1>
        <p class="text-slate-600 mt-1">{{ $venue->name }}</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('match-reports.store', $venue) }}" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">Water (optional)</label>
                <select name="water_id" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Whole complex</option>
                    @foreach ($venue->waters as $water)
                        <option value="{{ $water->id }}">{{ $water->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Title</label>
                <input name="title" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" value="{{ old('title') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Report</label>
                <textarea name="body" rows="8" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('body') }}</textarea>
            </div>
            <button class="px-5 py-3 rounded-md bg-emerald-700 text-white font-bold">Publish</button>
        </form>
    </div>
</x-app-layout>
