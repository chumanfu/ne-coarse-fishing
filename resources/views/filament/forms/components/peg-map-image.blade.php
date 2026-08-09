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
