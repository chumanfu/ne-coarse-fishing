<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">My session logs</h1>
                <p class="text-slate-600 mt-1">Personal catch reports and bankside notes.</p>
            </div>
            <a href="{{ route('sessions.create') }}" class="inline-flex px-4 py-2 rounded-md bg-sky-700 text-white font-semibold">Log a session</a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">
        @forelse ($sessions as $session)
            <a href="{{ route('sessions.show', $session) }}" class="block bg-white border-2 border-slate-300 rounded-xl p-5 hover:border-sky-700">
                <div class="flex flex-wrap justify-between gap-2">
                    <h2 class="font-bold text-lg text-slate-900">{{ $session->venue->name }}</h2>
                    <p class="text-sm font-semibold text-slate-700">{{ $session->fished_at->format('d M Y') }}</p>
                </div>
                <p class="text-sm text-slate-600 mt-1">
                    @if ($session->water) {{ $session->water->name }} · @endif
                    @if ($session->weather) {{ $session->weather }} · @endif
                    {{ $session->catches->count() }} catch entries
                </p>
                @if ($session->commentary)
                    <p class="text-sm text-slate-800 mt-2">{{ Str::limit($session->commentary, 160) }}</p>
                @endif
            </a>
        @empty
            <p class="text-slate-600">No sessions yet. Log your next trip from the bank.</p>
        @endforelse

        <div>{{ $sessions->links() }}</div>
    </div>
</x-app-layout>
