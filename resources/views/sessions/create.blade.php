<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Log a fishing session</h1>
        <p class="text-slate-600 mt-1">Capture date, peg, weather, catches and photos.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="sessionForm({
            venueId: '{{ old('venue_id', $venue->id ?? '') }}',
            watersByVenue: @js($watersJson),
            catches: @js(old('catches', [['species_id' => '', 'weight_lb' => '', 'bait' => '', 'quantity' => 1]]))
         })">
        <form method="POST" action="{{ route('sessions.store') }}" enctype="multipart/form-data" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold mb-1">Venue</label>
                <select name="venue_id" x-model="venueId" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Select venue</option>
                    @foreach ($venues as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
                @error('venue_id') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Water / lake</label>
                <select name="water_id" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Whole venue / not sure</option>
                    <template x-for="water in currentWaters" :key="water.id">
                        <option :value="water.id" x-text="water.name" :selected="water.id == '{{ old('water_id') }}'"></option>
                    </template>
                </select>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Date fished</label>
                    <input type="date" name="fished_at" value="{{ old('fished_at', now()->toDateString()) }}" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Duration (hours)</label>
                    <input type="number" name="duration_hours" value="{{ old('duration_hours') }}" min="1" max="72" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Weather</label>
                    <input name="weather" value="{{ old('weather') }}" placeholder="Overcast, light SW wind" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Peg number</label>
                    <input name="peg_number" value="{{ old('peg_number') }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Commentary / write-up</label>
                <textarea name="commentary" rows="5" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('commentary') }}</textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold">Fish caught</label>
                    <button type="button" @click="addCatch()" class="text-sm font-semibold text-sky-800">Add catch</button>
                </div>
                <template x-for="(catchItem, index) in catches" :key="index">
                    <div class="grid sm:grid-cols-4 gap-2 mb-2">
                        <select :name="`catches[${index}][species_id]`" x-model="catchItem.species_id" class="rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            <option value="">Species</option>
                            @foreach ($species as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" :name="`catches[${index}][weight_lb]`" x-model="catchItem.weight_lb" placeholder="Weight (lb)" class="rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                        <input :name="`catches[${index}][bait]`" x-model="catchItem.bait" placeholder="Bait" class="rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                        <div class="flex gap-2">
                            <input type="number" :name="`catches[${index}][quantity]`" x-model="catchItem.quantity" min="1" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            <button type="button" @click="removeCatch(index)" class="text-red-700 font-semibold text-sm">×</button>
                        </div>
                    </div>
                </template>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Photos (up to 6, mobile friendly)</label>
                <input type="file" name="photos[]" accept="image/*" capture="environment" multiple class="block w-full text-sm">
                @error('photos.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button class="px-5 py-3 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">Save session</button>
        </form>
    </div>

    @push('scripts')
        <script>
            function sessionForm({ venueId, watersByVenue, catches }) {
                return {
                    venueId,
                    watersByVenue,
                    catches,
                    get currentWaters() {
                        return this.watersByVenue[this.venueId] || [];
                    },
                    addCatch() {
                        this.catches.push({ species_id: '', weight_lb: '', bait: '', quantity: 1 });
                    },
                    removeCatch(index) {
                        this.catches.splice(index, 1);
                        if (!this.catches.length) this.addCatch();
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
