<x-filament-panels::page>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 sm:p-6">
        @if (isset($record))
            <livewire:venue-wizard :venue="$record" :admin="true" :key="'venue-wizard-'.$record->id" />
        @else
            <livewire:venue-wizard :admin="true" wire:key="venue-wizard-create" />
        @endif
    </div>
</x-filament-panels::page>
