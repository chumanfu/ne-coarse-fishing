@props([
    'src',
    'alt' => 'Pond map',
    'maxHeightClass' => 'max-h-80',
    'hint' => 'Scroll to zoom · drag to pan',
])

{{--
  Shrink-wrap the image so peg % coordinates are relative to the visible bitmap,
  not a letterboxed full-width object-contain box.
--}}
<div
    {{ $attributes->class(['space-y-2']) }}
    x-data="pondMapViewer({ minScale: 1, maxScale: 4 })"
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
        @pointerleave="onPointerUp($event)"
    >
        <div
            class="flex justify-center origin-center will-change-transform"
            :style="`transform: translate(${panX}px, ${panY}px) scale(${scale});`"
            :class="scale > 1 ? 'cursor-grab' : ''"
        >
            <div class="relative inline-block max-w-full">
                <img
                    src="{{ $src }}"
                    alt="{{ $alt }}"
                    draggable="false"
                    class="pointer-events-none block max-w-full h-auto w-auto {{ $maxHeightClass }}"
                >
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

@once
    <script>
        window.pondMapViewer = window.pondMapViewer || function pondMapViewer({ minScale = 1, maxScale = 4 } = {}) {
            return {
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
                    if (this.scale > 1 && (Math.abs(dx) > 2 || Math.abs(dy) > 2)) {
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
            };
        };
    </script>
@endonce
