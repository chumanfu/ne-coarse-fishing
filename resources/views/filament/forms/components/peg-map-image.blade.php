@php
    $waterId = $get('water_id');
    $water = $waterId ? \App\Models\Water::query()->find($waterId) : null;
    $mapUrl = $water?->mapImageUrl();
    $x = $get('map_x');
    $y = $get('map_y');
@endphp

<div
    x-data="{
        mapX: @js($x !== null && $x !== '' ? (float) $x : null),
        mapY: @js($y !== null && $y !== '' ? (float) $y : null),
        mapUrl: @js($mapUrl),
        place(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            if (! rect.width || ! rect.height) {
                return;
            }
            this.mapX = Math.min(100, Math.max(0, ((event.clientX - rect.left) / rect.width) * 100));
            this.mapY = Math.min(100, Math.max(0, ((event.clientY - rect.top) / rect.height) * 100));
            this.$wire.set('data.map_x', Number(this.mapX.toFixed(4)));
            this.$wire.set('data.map_y', Number(this.mapY.toFixed(4)));
        },
    }"
    class="space-y-2"
>
    <div x-show="! mapUrl" x-cloak>
        <p class="text-sm text-amber-800 bg-amber-50 border border-amber-300 rounded-lg p-3">
            Upload a pond map image on the water record first, then place the peg here.
        </p>
    </div>
    <div x-show="mapUrl" x-cloak>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Click the top-down pond image to place the peg.</p>
        <div class="flex justify-center rounded-lg border border-gray-300 overflow-hidden bg-gray-100">
            <div class="relative inline-block max-w-full select-none cursor-crosshair"
                 @click="place($event)">
                <img :src="mapUrl" alt="Pond map" class="pointer-events-none block max-h-96 max-w-full h-auto w-auto">
                <span
                    x-show="mapX !== null && mapY !== null"
                    x-cloak
                    class="pointer-events-none absolute z-10 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-sky-700 shadow-md ring-2 ring-sky-900/40"
                    :style="mapX !== null && mapY !== null ? `left:${mapX}%; top:${mapY}%;` : ''"
                ></span>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-1" x-show="mapX !== null && mapY !== null" x-cloak x-text="`Position ${Number(mapX).toFixed(1)}%, ${Number(mapY).toFixed(1)}%`"></p>
    </div>
</div>
