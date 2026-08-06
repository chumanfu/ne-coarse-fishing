<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-slate-600 mt-1">Your venues, managed waters and recent sessions.</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid gap-6 lg:grid-cols-2 xl:grid-cols-4">
        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-lg">Messages</h2>
                <a href="{{ route('messages.index') }}" class="text-sm font-semibold text-sky-800">Inbox</a>
            </div>
            <ul class="space-y-3 text-sm">
                @forelse ($myMessages as $thread)
                    <li>
                        <a href="{{ route('messages.show', $thread) }}" class="font-semibold text-slate-900 hover:underline">
                            {{ $thread->subject }}
                            @if ($thread->isUnreadForParticipant())
                                <span class="text-amber-700">· new</span>
                            @endif
                        </a>
                        <div class="text-slate-600">{{ $thread->last_message_at?->format('d M Y') }}</div>
                    </li>
                @empty
                    <li class="text-slate-600">No messages yet. <a href="{{ route('contact.create') }}" class="text-sky-800 font-semibold">Contact us</a></li>
                @endforelse
            </ul>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-lg">Recent sessions</h2>
                <a href="{{ route('sessions.create') }}" class="text-sm font-semibold text-sky-800">Log one</a>
            </div>
            <ul class="space-y-3 text-sm">
                @forelse ($mySessions as $session)
                    <li>
                        <a href="{{ route('sessions.show', $session) }}" class="font-semibold text-slate-900 hover:underline">{{ $session->venue->name }}</a>
                        <div class="text-slate-600">{{ $session->fished_at->format('d M Y') }}</div>
                    </li>
                @empty
                    <li class="text-slate-600">No sessions yet.</li>
                @endforelse
            </ul>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-lg">Favourites</h2>
                <a href="{{ route('venues.favourites') }}" class="text-sm font-semibold text-sky-800">View all</a>
            </div>
            <ul class="space-y-3 text-sm">
                @forelse ($favouriteVenues as $venue)
                    <li>
                        <a href="{{ route('venues.show', $venue) }}" class="font-semibold text-slate-900 hover:underline">{{ $venue->name }}</a>
                    </li>
                @empty
                    <li class="text-slate-600">Star a venue to save it here.</li>
                @endforelse
            </ul>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-lg">Submitted venues</h2>
                <a href="{{ route('venues.create') }}" class="text-sm font-semibold text-sky-800">Add</a>
            </div>
            <ul class="space-y-3 text-sm">
                @forelse ($myVenues as $venue)
                    <li>
                        <a href="{{ route('venues.show', $venue) }}" class="font-semibold text-slate-900 hover:underline">{{ $venue->name }}</a>
                        <div class="text-slate-600">{{ $venue->is_approved ? 'Approved' : 'Pending approval' }}</div>
                    </li>
                @empty
                    <li class="text-slate-600">You haven’t submitted a venue yet.</li>
                @endforelse
            </ul>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <h2 class="font-bold text-lg mb-3">Managed venues</h2>
            <ul class="space-y-3 text-sm">
                @forelse ($managedVenues as $venue)
                    <li>
                        <a href="{{ route('venues.show', $venue) }}" class="font-semibold text-slate-900 hover:underline">{{ $venue->name }}</a>
                        <div class="text-slate-600">
                            <a href="{{ route('pegs.create', $venue) }}" class="text-sky-800 font-semibold">Manage pegs</a>
                            ·
                            <a href="{{ route('match-reports.create', $venue) }}" class="text-emerald-800 font-semibold">Match report</a>
                            ·
                            <a href="{{ route('announcements.create', $venue) }}" class="text-emerald-800 font-semibold">Announcement</a>
                        </div>
                    </li>
                @empty
                    <li class="text-slate-600">Claim a venue to publish official updates.</li>
                @endforelse
            </ul>
            @role('super_admin')
                <a href="/admin" class="mt-4 inline-flex text-sm font-semibold text-amber-800">Open admin panel →</a>
            @endrole
        </section>
    </div>
</x-app-layout>
