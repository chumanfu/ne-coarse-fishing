@props([
    'src',
    'alt' => 'Pond map',
    'pegs' => [],
    'maxHeightClass' => 'max-h-80',
])

@php
    /** @var list<array<string, mixed>> $pegs */
    $pegs = array_values($pegs);
@endphp

<div
    class="space-y-2"
    x-data="pondMapExplorer({
        pegs: @js($pegs),
        minScale: 1,
        maxScale: 4,
    })"
>
    <div class="flex flex-wrap items-center gap-2">
        <button type="button"
                @click="zoomIn()"
                class="px-3 py-1.5 rounded-md border-2 border-slate-400 text-sm font-semibold hover:bg-slate-50"
                aria-label="Zoom in">
            Zoom in
        </button>
        <button type="button"
                @click="zoomOut()"
                class="px-3 py-1.5 rounded-md border-2 border-slate-400 text-sm font-semibold hover:bg-slate-50"
                aria-label="Zoom out">
            Zoom out
        </button>
        <button type="button"
                @click="resetView()"
                class="px-3 py-1.5 rounded-md border-2 border-slate-400 text-sm font-semibold hover:bg-slate-50"
                aria-label="Reset zoom">
            Reset
        </button>
        <p class="text-xs text-slate-500">Scroll to zoom · drag to pan · click a peg for details</p>
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
        >
            <div class="relative inline-block max-w-full">
                <img
                    src="{{ $src }}"
                    alt="{{ $alt }}"
                    draggable="false"
                    class="pointer-events-none block max-w-full h-auto w-auto {{ $maxHeightClass }}"
                >
                <template x-for="peg in pegs" :key="'peg-'+peg.id">
                    <button
                        type="button"
                        class="absolute z-10 h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-sky-700 shadow ring-2 ring-sky-900/30 hover:scale-125 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-sky-700"
                        :class="selectedPegId === peg.id ? 'scale-125 ring-amber-400' : ''"
                        :style="`left:${peg.x}%; top:${peg.y}%;`"
                        :title="peg.label"
                        :aria-label="'Peg ' + peg.label"
                        @click.stop="selectPeg(peg)"
                        @pointerdown.stop
                    ></button>
                </template>
            </div>
        </div>
    </div>

    <div
        x-show="selectedPeg"
        x-cloak
        x-transition
        class="rounded-lg border-2 border-sky-400 bg-sky-50/60 p-4"
        role="dialog"
        aria-modal="false"
        :aria-label="selectedPeg ? ('Details for peg ' + selectedPeg.label) : ''"
    >
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h4 class="font-bold text-slate-900" x-text="selectedPeg?.label"></h4>
                <p class="text-sm text-slate-600 mt-1" x-show="selectedPeg?.description" x-text="selectedPeg?.description"></p>
            </div>
            <button type="button"
                    @click="selectedPeg = null; selectedPegId = null"
                    class="text-sm font-semibold text-slate-700 hover:text-sky-800 shrink-0">
                Close
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm mb-3">
            <div class="rounded-md border border-slate-300 bg-white px-3 py-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fish caught</p>
                <p class="text-lg font-bold text-slate-900" x-text="selectedPeg?.fish_caught ?? 0"></p>
                <p class="text-xs text-slate-500">all time</p>
            </div>
            <div class="rounded-md border border-slate-300 bg-white px-3 py-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sessions</p>
                <p class="text-lg font-bold text-slate-900" x-text="selectedPeg?.session_count ?? 0"></p>
                <p class="text-xs text-slate-500">logged here</p>
            </div>
            <div class="rounded-md border border-slate-300 bg-white px-3 py-2 col-span-2 sm:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Heaviest</p>
                <p class="text-lg font-bold text-slate-900"
                   x-text="selectedPeg?.heaviest_lb != null ? (Number(selectedPeg.heaviest_lb).toFixed(2) + ' lb') : '—'"></p>
                <p class="text-xs text-slate-500">recorded weight</p>
            </div>
        </div>

        <div class="mb-3">
            <p class="text-sm font-semibold text-slate-800 mb-1">Top species</p>
            <template x-if="! selectedPeg?.top_species?.length">
                <p class="text-sm text-slate-600">No catches logged on this peg yet.</p>
            </template>
            <ul class="space-y-1" x-show="selectedPeg?.top_species?.length">
                <template x-for="(species, index) in (selectedPeg?.top_species || [])" :key="'sp-'+index">
                    <li class="flex items-center justify-between gap-3 text-sm border border-slate-200 rounded-md bg-white px-3 py-1.5">
                        <span class="font-semibold text-slate-900">
                            <span class="text-slate-500 font-normal" x-text="(index + 1) + '.'"></span>
                            <span x-text="species.name"></span>
                        </span>
                        <span class="text-slate-600" x-text="species.total + (species.total === 1 ? ' fish' : ' fish')"></span>
                    </li>
                </template>
            </ul>
        </div>

        <div x-show="selectedPeg?.photos?.length">
            <p class="text-sm font-semibold text-slate-800 mb-2">Peg photos</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <template x-for="(photo, index) in (selectedPeg?.photos || [])" :key="'ph-'+index">
                    <button
                        type="button"
                        class="relative block w-full text-left"
                        @click="$store.photoLightbox.open(selectedPeg.photos, index, 'Peg photo')"
                    >
                        <img :src="photo.url" alt="" class="rounded-md object-cover h-20 w-full border border-slate-300 hover:border-sky-700">
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function pondMapExplorer({ pegs, minScale = 1, maxScale = 4 }) {
                return {
                    pegs: pegs || [],
                    scale: 1,
                    panX: 0,
                    panY: 0,
                    minScale,
                    maxScale,
                    selectedPeg: null,
                    selectedPegId: null,
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
                        this.scale = Math.min(this.maxScale, Math.max(this.minScale, Number(next.toFixed(2))));
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
                    selectPeg(peg) {
                        if (this.dragMoved) {
                            return;
                        }
                        this.selectedPeg = peg;
                        this.selectedPegId = peg.id;
                    },
                };
            }
        </script>
    @endpush
@endonce
