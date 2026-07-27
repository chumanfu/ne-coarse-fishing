<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Suggest an edit</h1>
        <p class="text-slate-600 mt-1">Propose changes to {{ $venue->name }}. Match reports, tactics and sessions are not included — a fishery manager or admin will review before anything goes live.</p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <livewire:venue-wizard :venue="$venue" :edit-request="true" />
    </div>
</x-app-layout>
