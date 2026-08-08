@php
    $waterId = $get('water_id');
    $water = $waterId ? \App\Models\Water::query()->find($waterId) : null;
    $mapUrl = $water?->mapImageUrl();
    $x = $get('map_x');
    $y = $get('map_y');
@endphp

<div
    x-data="{
        x: @js($x !== null && $x !== '' ? (float) $x : null),
        y: @js($y !== null && $y !== '' ? (float) $y : null),
        mapUrl: @js($mapUrl),
        place(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            if (! rect.width || ! rect.height) {
                return;
            }
            this.x = Math.min(100, Math.max(0, ((event.clientX - rect.left) / rect.width) * 100));
            this.y = Math.min(100, Math.max(0, ((event.clientY - rect.top) / rect.height) * 100));
            this.$wire.set('data.map_x', Number(this.x.toFixed(4)));
            this.$wire.set('data.map_y', Number(this.y.toFixed(4)));
        },
    }"
    class="space-y-2"
>
    <template x-if="! mapUrl">
        <p class="text-sm text-amber-800 bg-amber-50 border border-amber-300 rounded-lg p-3">
            Upload a pond map image on the water record first, then place the peg here.
        </p>
    </template>
    <template x-if="mapUrl">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Click the top-down pond image to place the peg.</p>
            <div class="relative w-full rounded-lg border border-gray-300 overflow-hidden bg-gray-100 select-none"
                 @click="place($event)">
                <img :src="mapUrl" alt="Pond map" class="block w-full h-auto max-h-96 object-contain mx-auto">
                <template x-if="x !== null && y !== null">
                    <span
                        class="absolute h-4 w-4 -ml-2 -mt-2 rounded-full border-2 border-white bg-sky-700 shadow"
                        :style="`left:${x}%; top:${y}%;`"
                    ></span>
                </template>
            </div>
            <p class="text-xs text-gray-500 mt-1" x-show="x !== null && y !== null" x-text="`Position ${Number(x).toFixed(1)}%, ${Number(y).toFixed(1)}%`"></p>
        </div>
    </template>
</div>
