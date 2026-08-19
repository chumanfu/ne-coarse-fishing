@php
    $waterId = $get('water_id');
    $water = $waterId ? \App\Models\Water::query()->find($waterId) : null;
    $mapUrl = $water?->mapImageUrl();
    $x = $get('map_x');
    $y = $get('map_y');
@endphp

{{-- Remount when the water (or map URL) changes so Alpine does not keep a stale empty mapUrl. --}}
<div wire:key="peg-map-{{ $waterId ?: 'none' }}-{{ $mapUrl ? md5($mapUrl) : 'empty' }}" class="space-y-2">
    @if (! $waterId)
        <p class="text-sm text-gray-600 dark:text-gray-300 rounded-lg border border-gray-300 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-800">
            Select a water first to place the peg on its pond map.
        </p>
    @elseif (! $mapUrl)
        <p class="text-sm text-amber-800 bg-amber-50 border border-amber-300 rounded-lg p-3 dark:bg-amber-950/40 dark:text-amber-200 dark:border-amber-700">
            Upload a pond map image on the water record first, then place the peg here.
        </p>
    @else
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Zoom in for precision, then click the top-down pond image to place the peg.
        </p>
        <x-pond-map-placer
            :src="$mapUrl"
            :alt="($water->name ?? 'Pond').' map'"
            :map-x="$x"
            :map-y="$y"
            :sync-wire="true"
            max-height-class="max-h-96"
            hint="Scroll to zoom · drag to pan when zoomed · click to place"
            class="fi-pond-map-placer"
        />

        @assets
        <style>
            /*
             * Filament compiles its own Tailwind bundle that does not include the utility
             * classes used inside pond-map-placer.blade.php.  The rules below replicate
             * exactly the Tailwind utilities that the component relies on so that the pond
             * map image is constrained to its container and the peg-location pin is visible.
             */

            /* ── image constraints ─────────────────────────────────────── */
            .fi-pond-map-placer img {
                display: block;
                max-width: 100%;  /* replaces max-w-full */
                height: auto;     /* replaces h-auto */
                width: auto;      /* replaces w-auto */
                max-height: 24rem; /* replaces max-h-96 (96 × 0.25rem = 24rem) */
            }

            /* ── peg-location pin ──────────────────────────────────────── */
            .fi-pond-map-placer .peg-pin {
                pointer-events: none;
                position: absolute;
                z-index: 10;
                height: 1.25rem;  /* h-5 */
                width: 1.25rem;   /* w-5 */
                transform: translate(-50%, -50%); /* -translate-x-1/2 -translate-y-1/2 */
                border-radius: 9999px;            /* rounded-full */
                border-width: 2px;
                border-style: solid;
                border-color: #fff;               /* border-white */
                background-color: #0369a1;        /* bg-sky-700 */
                box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1); /* shadow-md */
                outline: 2px solid rgb(12 74 110 / 0.4); /* ring-2 ring-sky-900/40 */
                outline-offset: 0;
            }

            /* ── x-cloak: hide elements until Alpine has initialised ────── */
            .fi-pond-map-placer [x-cloak] {
                display: none !important;
            }
        </style>
        @endassets
        @if (filled($mapUrl))
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Map source:
                <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-sky-700 underline dark:text-sky-300">
                    open image
                </a>
            </p>
        @endif
    @endif
</div>
