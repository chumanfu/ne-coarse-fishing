<x-app-layout>
    <x-page-hero
        :image="asset('images/sessions/hero-bankside.jpg')"
        alt="Angler with a catch on a North East bank"
        eyebrow="Session logs"
        title="My session logs"
        lead="Personal catch reports, peg notes and bankside write-ups — yours to revisit and share on venue guides."
        image-position="center 30%"
    >
        <x-slot:actions>
            <a href="{{ route('sessions.create') }}" class="page-hero__btn page-hero__btn--primary">Log a session</a>
            <a href="{{ route('venues.index') }}" class="page-hero__btn page-hero__btn--ghost">Browse venues</a>
        </x-slot:actions>

        @if ($sessions->total() > 0)
            <p><em>{{ $sessions->total() }} {{ Str::plural('session', $sessions->total()) }} logged</em></p>
        @endif
    </x-page-hero>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">
        @forelse ($sessions as $session)
            <div class="bg-white border-2 border-slate-300 rounded-xl p-5 dark:bg-slate-900 dark:border-slate-700">
                <div class="flex flex-wrap justify-between gap-2">
                    <a href="{{ route('sessions.show', $session) }}" class="font-bold text-lg text-slate-900 hover:underline dark:text-slate-100">{{ $session->venue->name }}</a>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $session->fished_at->format('d M Y') }}</p>
                </div>
                <p class="text-sm text-slate-600 mt-1 dark:text-slate-400">
                    @if ($session->water) {{ $session->water->name }} · @endif
                    @if ($session->weather) {{ $session->weather }} · @endif
                    {{ $session->catches->count() }} catch entries
                </p>
                @if ($session->commentary)
                    <p class="text-sm text-slate-800 mt-2 dark:text-slate-200">{{ Str::limit($session->commentary, 160) }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-3 text-sm font-semibold">
                    <a href="{{ route('sessions.show', $session) }}" class="text-sky-800 hover:underline dark:text-sky-300">View</a>
                    <a href="{{ route('sessions.edit', $session) }}" class="text-sky-800 hover:underline dark:text-sky-300">Edit</a>
                </div>
            </div>
        @empty
            <div class="bg-white border-2 border-dashed border-slate-300 rounded-xl p-8 text-center dark:bg-slate-900 dark:border-slate-700">
                <p class="text-slate-700 dark:text-slate-300">No sessions yet. Log your next trip from the bank.</p>
                <a href="{{ route('sessions.create') }}" class="inline-flex mt-4 px-4 py-2 rounded-md bg-sky-700 text-white font-semibold hover:bg-sky-800">Log a session</a>
            </div>
        @endforelse

        <div>{{ $sessions->links() }}</div>
    </div>
</x-app-layout>
