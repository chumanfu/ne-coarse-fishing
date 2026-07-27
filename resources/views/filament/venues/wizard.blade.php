<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 sm:p-6">
        @if (isset($this->record))
            <livewire:venue-wizard :venue="$this->record->id" :admin="true" :key="'venue-wizard-'.$this->record->id" />
        @else
            <livewire:venue-wizard :admin="true" wire:key="venue-wizard-create" />
        @endif
    </div>
</x-filament-panels::page>
