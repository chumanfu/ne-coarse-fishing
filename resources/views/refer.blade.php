<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Refer a friend</h1>
        <p class="text-slate-600 mt-1">Share NE Coarse Fishing with another angler.</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white border-2 border-slate-300 rounded-xl p-6 space-y-5">
            <p class="text-slate-700">Know someone looking for day-ticket lakes, club waters or local tackle advice in the North East? Pass them the link.</p>
            <p class="text-sm font-mono break-all bg-slate-50 border border-slate-300 rounded-md px-3 py-2 text-slate-800">{{ $shareUrl }}</p>
            <x-share-links
                :url="$shareUrl"
                :title="$shareTitle"
                :text="$shareText"
                label="Share via"
            />
            <p class="text-sm text-slate-600">Instagram doesn’t allow direct link sharing from the web — tap Instagram to copy the link, then paste it into a DM or story.</p>
        </div>
    </div>
</x-app-layout>
