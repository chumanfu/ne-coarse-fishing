@php
    $editing = $peg !== null;
    $watersPayload = $venue->waters->map(fn ($water) => [
        'id' => $water->id,
        'name' => $water->name,
        'map_url' => $water->mapImageUrl(),
    ])->values();
    $initialWaterId = (int) old('water_id', $editing ? $peg->water_id : ($venue->waters->first()?->id ?? 0));
    $initialX = old('map_x', $editing ? $peg->map_x : null);
    $initialY = old('map_y', $editing ? $peg->map_y : null);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">
            {{ $editing ? 'Edit peg' : 'Add peg' }} · {{ $venue->name }}
        </h1>
        <p class="text-slate-600 mt-1">
            Place the peg on the pond’s top-down map image. Upload a map for each water on the venue page first if needed.
        </p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="managerPegForm({
            waters: @js($watersPayload),
            waterId: @js($initialWaterId),
            x: @js($initialX !== null ? (float) $initialX : null),
            y: @js($initialY !== null ? (float) $initialY : null),
         })">
        <form method="POST"
              action="{{ $editing ? route('pegs.update', [$venue, $peg]) : route('pegs.store', $venue) }}"
              enctype="multipart/form-data"
              class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-semibold mb-1">Water / lake</label>
                <select name="water_id" required x-model.number="waterId" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    <option value="">Select water</option>
                    @foreach ($venue->waters as $water)
                        <option value="{{ $water->id }}">{{ $water->name }}</option>
                    @endforeach
                </select>
                @error('water_id') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Peg number</label>
                    <input name="number" value="{{ old('number', $editing ? $peg->number : '') }}" placeholder="e.g. 12" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    @error('number') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Peg name</label>
                    <input name="name" value="{{ old('name', $editing ? $peg->name : '') }}" placeholder="e.g. Island, Car park end" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    @error('name') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Location on pond map</label>
                <p class="text-sm text-slate-600 mb-2">Click the top-down image to place the peg pin.</p>

                <template x-if="! selectedWater">
                    <p class="text-sm text-slate-600 border-2 border-dashed border-slate-300 rounded-lg p-4">Select a water first.</p>
                </template>
                <template x-if="selectedWater && ! selectedWater.map_url">
                    <p class="text-sm text-amber-900 bg-amber-50 border-2 border-amber-400 rounded-lg p-4">
                        This water does not have a pond map image yet.
                        <a href="{{ route('venues.show', $venue) }}" class="font-semibold underline">Upload one on the venue page</a>
                        before placing pegs.
                    </p>
                </template>
                <template x-if="selectedWater && selectedWater.map_url">
                    <div>
                        <div class="relative w-full rounded-lg border-2 border-slate-400 overflow-hidden bg-slate-200 select-none"
                             @click="placePeg($event)">
                            <img :src="selectedWater.map_url" alt="Pond map" class="block w-full h-auto max-h-[28rem] object-contain mx-auto bg-slate-100">
                            <template x-if="x !== null && y !== null">
                                <span
                                    class="absolute h-4 w-4 -ml-2 -mt-2 rounded-full border-2 border-white bg-sky-700 shadow"
                                    :style="`left:${x}%; top:${y}%;`"
                                    aria-hidden="true"
                                ></span>
                            </template>
                        </div>
                        <input type="hidden" name="map_x" :value="x ?? ''">
                        <input type="hidden" name="map_y" :value="y ?? ''">
                        <p class="text-xs text-slate-500 mt-2" x-show="x !== null && y !== null" x-text="`Position ${Number(x).toFixed(1)}%, ${Number(y).toFixed(1)}%`"></p>
                    </div>
                </template>
                @error('map_x') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                @error('map_y') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Peg photos (optional, up to 4)</label>
                <p class="text-sm text-slate-600 mb-2">Photos help anglers recognise the peg. They stay with the peg once saved.</p>

                @if ($editing && $peg->photos->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3"
                         x-data="{
                            kept: @js($peg->photos->pluck('id')->values()->all()),
                            toggle(id) {
                                if (this.kept.includes(id)) {
                                    this.kept = this.kept.filter((item) => item !== id);
                                } else {
                                    this.kept.push(id);
                                }
                            },
                         }">
                        <template x-for="id in kept" :key="'keep-'+id">
                            <input type="hidden" name="keep_photo_ids[]" :value="id">
                        </template>
                        @foreach ($peg->photos as $photo)
                            <div class="relative rounded-md overflow-hidden border-2 border-slate-300"
                                 :class="kept.includes({{ $photo->id }}) ? '' : 'opacity-40 ring-2 ring-red-500'">
                                <img src="{{ $photo->url() }}" alt="" class="h-24 w-full object-cover">
                                <button type="button"
                                        @click="toggle({{ $photo->id }})"
                                        class="absolute inset-x-0 bottom-0 bg-black/60 text-white text-xs font-semibold py-1"
                                        x-text="kept.includes({{ $photo->id }}) ? 'Keep' : 'Remove'"></button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <input type="file" name="photos[]" accept="image/*" capture="environment" multiple class="block w-full text-sm">
                @error('photos') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                @error('photos.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button class="px-5 py-3 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">
                    {{ $editing ? 'Save changes' : 'Save peg' }}
                </button>
                <a href="{{ route('venues.show', $venue) }}" class="px-5 py-3 rounded-md border-2 border-slate-400 font-semibold">Cancel</a>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            function managerPegForm({ waters, waterId, x, y }) {
                return {
                    waters,
                    waterId: Number(waterId) || '',
                    x: x === null || x === undefined || x === '' ? null : Number(x),
                    y: y === null || y === undefined || y === '' ? null : Number(y),
                    get selectedWater() {
                        return this.waters.find((water) => Number(water.id) === Number(this.waterId)) || null;
                    },
                    placePeg(event) {
                        const rect = event.currentTarget.getBoundingClientRect();
                        if (! rect.width || ! rect.height) {
                            return;
                        }

                        this.x = Math.min(100, Math.max(0, ((event.clientX - rect.left) / rect.width) * 100));
                        this.y = Math.min(100, Math.max(0, ((event.clientY - rect.top) / rect.height) * 100));
                    },
                };
            }
        </script>
    </x-slot>
</x-app-layout>
