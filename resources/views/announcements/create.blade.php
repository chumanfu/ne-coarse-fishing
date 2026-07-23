<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Post official update</h1>
        <p class="text-slate-600 mt-1">{{ $venue->name }}</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('announcements.store', $venue) }}" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">Type</label>
                <select name="type" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="announcement">Club announcement</option>
                    <option value="stocking">Stocking update</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Title</label>
                <input name="title" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" value="{{ old('title') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Details</label>
                <textarea name="body" rows="8" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('body') }}</textarea>
            </div>
            <button class="px-5 py-3 rounded-md bg-emerald-700 text-white font-bold">Publish</button>
        </form>
    </div>
</x-app-layout>
