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
            mapX: @js($initialX !== null ? (float) $initialX : null),
            mapY: @js($initialY !== null ? (float) $initialY : null),
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
                <p class="text-sm text-slate-600 mb-2">Zoom in for precision, then click the top-down image to place the peg pin.</p>

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
                <div x-show="selectedWater && selectedWater.map_url" x-cloak class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="zoomIn()" class="px-3 py-1.5 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold hover:bg-slate-50">Zoom in</button>
                        <button type="button" @click="zoomOut()" class="px-3 py-1.5 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold hover:bg-slate-50">Zoom out</button>
                        <button type="button" @click="resetView()" class="px-3 py-1.5 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold hover:bg-slate-50">Reset</button>
                        <p class="text-xs text-slate-500">Scroll to zoom · drag to pan when zoomed · click to place</p>
                    </div>
                    <div
                        class="relative overflow-hidden rounded-lg border-2 border-slate-400 bg-slate-100 touch-none select-none"
                        style="min-height: 12rem;"
                        @wheel.prevent="onWheel($event)"
                        @pointerdown="onPointerDown($event)"
                        @pointermove="onPointerMove($event)"
                        @pointerup="onPointerUp($event)"
                        @pointercancel="onPointerUp($event)"
                        @pointerleave="onPointerUp($event)"
                    >
                        <div
                            class="flex justify-center origin-center will-change-transform"
                            :style="`transform: translate(${panX}px, ${panY}px) scale(${scale});`"
                            :class="scale > 1 ? 'cursor-grab' : 'cursor-crosshair'"
                        >
                            <div class="relative inline-block max-w-full" @click="placePeg($event)">
                                <img :src="selectedWater?.map_url"
                                     alt="Pond map"
                                     draggable="false"
                                     class="pointer-events-none block max-h-[28rem] max-w-full h-auto w-auto bg-slate-100">
                                <span
                                    x-show="mapX !== null && mapY !== null"
                                    x-cloak
                                    class="pointer-events-none absolute z-10 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-sky-700 shadow-md ring-2 ring-sky-900/40"
                                    :style="mapX !== null && mapY !== null ? `left:${mapX}%; top:${mapY}%;` : ''"
                                    aria-hidden="true"
                                ></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="map_x" :value="mapX ?? ''">
                    <input type="hidden" name="map_y" :value="mapY ?? ''">
                    <p class="text-xs text-slate-500" x-show="mapX !== null && mapY !== null" x-cloak x-text="`Position ${Number(mapX).toFixed(1)}%, ${Number(mapY).toFixed(1)}%`"></p>
                </div>
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
            function managerPegForm({ waters, waterId, mapX, mapY }) {
                return {
                    waters,
                    waterId: Number(waterId) || '',
                    mapX: mapX === null || mapX === undefined || mapX === '' ? null : Number(mapX),
                    mapY: mapY === null || mapY === undefined || mapY === '' ? null : Number(mapY),
                    scale: 1,
                    panX: 0,
                    panY: 0,
                    minScale: 1,
                    maxScale: 5,
                    dragging: false,
                    dragMoved: false,
                    pointerId: null,
                    lastX: 0,
                    lastY: 0,
                    get selectedWater() {
                        return this.waters.find((water) => Number(water.id) === Number(this.waterId)) || null;
                    },
                    init() {
                        this.$watch('waterId', () => {
                            this.mapX = null;
                            this.mapY = null;
                            this.resetView();
                        });
                    },
                    zoomIn() {
                        this.setScale(this.scale + 0.35);
                    },
                    zoomOut() {
                        this.setScale(this.scale - 0.35);
                    },
                    resetView() {
                        this.scale = 1;
                        this.panX = 0;
                        this.panY = 0;
                    },
                    setScale(next) {
                        this.scale = Math.min(this.maxScale, Math.max(this.minScale, Number(Number(next).toFixed(2))));
                        if (this.scale === 1) {
                            this.panX = 0;
                            this.panY = 0;
                        }
                    },
                    onWheel(event) {
                        const delta = event.deltaY > 0 ? -0.2 : 0.2;
                        this.setScale(this.scale + delta);
                    },
                    onPointerDown(event) {
                        if (event.button !== undefined && event.button !== 0) {
                            return;
                        }
                        this.dragging = true;
                        this.dragMoved = false;
                        this.pointerId = event.pointerId;
                        this.lastX = event.clientX;
                        this.lastY = event.clientY;
                        event.currentTarget.setPointerCapture?.(event.pointerId);
                    },
                    onPointerMove(event) {
                        if (! this.dragging || this.pointerId !== event.pointerId) {
                            return;
                        }
                        const dx = event.clientX - this.lastX;
                        const dy = event.clientY - this.lastY;
                        if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
                            this.dragMoved = true;
                        }
                        if (this.scale > 1) {
                            this.panX += dx;
                            this.panY += dy;
                        }
                        this.lastX = event.clientX;
                        this.lastY = event.clientY;
                    },
                    onPointerUp(event) {
                        if (this.pointerId !== null && event.pointerId !== this.pointerId) {
                            return;
                        }
                        this.dragging = false;
                        this.pointerId = null;
                    },
                    placePeg(event) {
                        if (this.dragMoved) {
                            return;
                        }

                        const rect = event.currentTarget.getBoundingClientRect();
                        if (! rect.width || ! rect.height) {
                            return;
                        }

                        this.mapX = Math.min(100, Math.max(0, ((event.clientX - rect.left) / rect.width) * 100));
                        this.mapY = Math.min(100, Math.max(0, ((event.clientY - rect.top) / rect.height) * 100));
                    },
                };
            }
        </script>
    </x-slot>
</x-app-layout>
