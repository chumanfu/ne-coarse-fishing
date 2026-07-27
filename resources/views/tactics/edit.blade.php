<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Edit tactics tip</h1>
        <p class="text-slate-600 mt-1">{{ $venue->name }}</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('tactics.update', $tactic) }}" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-semibold mb-1">Tactics tip</label>
                <textarea name="body" rows="6" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('body', $tactic->body) }}</textarea>
                @error('body') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Water / lake</label>
                    <select name="water_id" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                        <option value="">Whole venue / not sure</option>
                        @foreach ($venue->waters as $water)
                            <option value="{{ $water->id }}" @selected(old('water_id', $tactic->water_id) == $water->id)>{{ $water->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Peg number</label>
                    <input name="peg_number" value="{{ old('peg_number', $tactic->peg_number) }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Date visited (optional)</label>
                    <input type="date" name="fished_at" value="{{ old('fished_at', optional($tactic->fished_at)?->toDateString()) }}" max="{{ now()->toDateString() }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button class="px-5 py-3 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">Save changes</button>
                <a href="{{ route('venues.show', $venue) }}" class="px-5 py-3 rounded-md border-2 border-slate-400 font-semibold text-slate-800">Cancel</a>
            </div>
        </form>

        @can('delete', $tactic)
            <form method="POST" action="{{ route('tactics.destroy', $tactic) }}" class="mt-4" onsubmit="return confirm('Delete this tactics tip?')">
                @csrf
                @method('DELETE')
                <button class="text-red-700 font-semibold text-sm">Delete tactic</button>
            </form>
        @endcan
    </div>
</x-app-layout>
