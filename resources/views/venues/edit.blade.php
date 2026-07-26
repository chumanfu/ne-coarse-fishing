<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Edit venue</h1>
        <p class="text-slate-600 mt-1">Update the map pin, details and waters for {{ $venue->name }}.</p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <livewire:venue-wizard :venue="$venue" />
    </div>
</x-app-layout>
