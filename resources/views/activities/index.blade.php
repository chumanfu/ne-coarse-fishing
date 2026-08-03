<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Activity</h1>
            <p class="text-slate-600 mt-1">All recent venues, sessions, tactics, clubs and tackle shops from around the region.</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-3">
            @forelse ($activities as $activity)
                <x-activity-row :activity="$activity" />
            @empty
                <p class="text-slate-600">No activity yet — log a session or add a venue to get things started.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $activities->links() }}</div>
    </div>
</x-app-layout>
