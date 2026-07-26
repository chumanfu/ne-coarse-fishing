<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Submit a venue</h1>
        <p class="text-slate-600 mt-1">Add a lake, complex or stretch in a few short steps. New submissions go into the moderation queue.</p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <livewire:venue-wizard />
    </div>
</x-app-layout>
