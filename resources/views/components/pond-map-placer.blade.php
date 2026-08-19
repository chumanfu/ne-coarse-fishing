@props([
    'src',
    'alt' => 'Pond map',
    'mapX' => null,
    'mapY' => null,
    'syncWire' => false,
    'xInput' => null,
    'yInput' => null,
    'maxHeightClass' => 'max-h-[28rem]',
    'hint' => 'Zoom in for precision, then click to place the peg pin.',
])

@php
    $initialX = $mapX !== null && $mapX !== '' ? (float) $mapX : null;
    $initialY = $mapY !== null && $mapY !== '' ? (float) $mapY : null;
@endphp

<div
    {{ $attributes->class(['space-y-2']) }}
    x-data="pondMapPlacer({
        mapX: @js($initialX),
        mapY: @js($initialY),
        syncWire: @js((bool) $syncWire),
        minScale: 1,
        maxScale: 5,
    })"
>
    <div class="flex flex-wrap items-center gap-2">
        <button type="button"
                @click="zoomIn()"
                class="px-3 py-1.5 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold text-slate-900 hover:bg-slate-50"
                aria-label="Zoom in">
            Zoom in
        </button>
        <button type="button"
                @click="zoomOut()"
                class="px-3 py-1.5 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold text-slate-900 hover:bg-slate-50"
                aria-label="Zoom out">
            Zoom out
        </button>
        <button type="button"
                @click="resetView()"
                class="px-3 py-1.5 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold text-slate-900 hover:bg-slate-50"
                aria-label="Reset zoom">
            Reset
        </button>
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    </div>

    <div
        class="relative overflow-hidden rounded-lg border-2 border-slate-400 bg-slate-100 touch-none select-none"
        style="min-height: 12rem;"
        @wheel.prevent="onWheel($event)"
        @pointerdown="onPointerDown($event)"
        @pointermove="onPointerMove($event)"
        @pointerup="onPointerUp($event)"
        @pointercancel="onPointerUp($event)"
    >
        <div
            class="flex justify-center origin-center will-change-transform"
            :style="`transform: translate(${panX}px, ${panY}px) scale(${scale});`"
            :class="scale > 1 ? 'cursor-grab' : 'cursor-crosshair'"
        >
            <div x-ref="mapLayer" class="relative inline-block max-w-full">
                <img
                    src="{{ $src }}"
                    alt="{{ $alt }}"
                    draggable="false"
                    class="pointer-events-none block max-w-full h-auto w-auto {{ $maxHeightClass }}"
                >
                <span
                    x-show="mapX !== null && mapY !== null"
                    x-cloak
                    class="peg-pin pointer-events-none absolute z-10 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-sky-700 shadow-md ring-2 ring-sky-900/40"
                    :style="mapX !== null && mapY !== null ? `left:${mapX}%; top:${mapY}%;` : ''"
                    aria-hidden="true"
                ></span>
            </div>
        </div>
    </div>

    @if ($xInput)
        <input type="hidden" name="{{ $xInput }}" :value="mapX ?? ''">
    @endif
    @if ($yInput)
        <input type="hidden" name="{{ $yInput }}" :value="mapY ?? ''">
    @endif

    <p class="text-xs text-slate-500"
       x-show="mapX !== null && mapY !== null"
       x-cloak
       x-text="`Position ${Number(mapX).toFixed(1)}%, ${Number(mapY).toFixed(1)}%`"></p>
</div>

@once
    <script>
        window.pondMapPlacer = window.pondMapPlacer || function pondMapPlacer({
            mapX = null,
            mapY = null,
            syncWire = false,
            minScale = 1,
            maxScale = 5,
        } = {}) {
            return {
                mapX: mapX === null || mapX === undefined || mapX === '' ? null : Number(mapX),
                mapY: mapY === null || mapY === undefined || mapY === '' ? null : Number(mapY),
                syncWire: Boolean(syncWire),
                scale: 1,
                panX: 0,
                panY: 0,
                minScale,
                maxScale,
                dragging: false,
                dragMoved: false,
                pointerId: null,
                lastX: 0,
                lastY: 0,
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
                    if (event.target.closest('button, a, input, select, textarea, label')) {
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
                    if (this.scale > 1 && (Math.abs(dx) > 3 || Math.abs(dy) > 3)) {
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

                    // Place on pointerup — viewport pointer capture prevents reliable child clicks.
                    const shouldPlace = this.dragging && ! this.dragMoved;
                    this.dragging = false;
                    this.pointerId = null;

                    if (shouldPlace) {
                        this.placeAtClientPoint(event.clientX, event.clientY);
                    }
                },
                placeAtClientPoint(clientX, clientY) {
                    const layer = this.$refs.mapLayer;
                    if (! layer) {
                        return;
                    }

                    const rect = layer.getBoundingClientRect();
                    if (! rect.width || ! rect.height) {
                        return;
                    }

                    if (
                        clientX < rect.left
                        || clientX > rect.right
                        || clientY < rect.top
                        || clientY > rect.bottom
                    ) {
                        return;
                    }

                    this.mapX = Math.min(100, Math.max(0, ((clientX - rect.left) / rect.width) * 100));
                    this.mapY = Math.min(100, Math.max(0, ((clientY - rect.top) / rect.height) * 100));

                    const x = Number(this.mapX.toFixed(4));
                    const y = Number(this.mapY.toFixed(4));

                    if (this.syncWire && this.$wire) {
                        this.$wire.set('data.map_x', x);
                        this.$wire.set('data.map_y', y);
                    }

                    this.$dispatch('pond-map-placed', { x, y });
                },
            };
        };
    </script>
@endonce
