<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Rigs</h1>
        <p class="text-slate-600 mt-1">Work out the shot a float needs, move it on the line, and keep the rig for the venues you fish.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">
        @if (session('status'))
            <p class="bg-moss-soft border-2 border-moss text-moss-dark font-semibold rounded-xl px-4 py-3" role="status">{{ session('status') }}</p>
        @endif

        <a href="{{ route('tools.rigs.create') }}" class="inline-flex items-center px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">
            Create New Rig
        </a>

        @guest
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <p class="text-sm text-slate-700">
                    <a href="{{ route('register') }}" class="font-semibold text-sky-800 hover:underline">Create a free account</a>
                    or <a href="{{ route('login') }}" class="font-semibold text-sky-800 hover:underline">log in</a>
                    to see the rigs you have saved.
                </p>
            </section>
        @endguest

        @auth
            @if ($rigs->isEmpty())
                <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                    <p class="text-sm text-slate-700">You have no saved rigs yet. Create one, place the shot, and save it here.</p>
                </section>
            @else
                <ul class="space-y-4">
                    @foreach ($rigs as $rig)
                        <li class="bg-white border-2 border-slate-300 rounded-xl p-5">
                            <h2 class="text-lg font-bold text-slate-900">{{ $rig->displayName() }}</h2>
                            <p class="mt-1 text-sm text-slate-700">
                                {{ $rig->float_name }} · {{ $rig->float_size }} · {{ $rig->floatTypeLabel() }} · {{ $rig->depthLabel() }}
                            </p>
                            <p class="text-sm text-slate-600">{{ $rig->patternLabel() }}</p>
                            <p class="mt-2 text-sm text-slate-800">
                                <span class="font-semibold">Venues:</span> {{ $rig->venueSummary() }}
                            </p>
                            @if ($rig->pegSummary() !== '')
                                <p class="text-sm text-slate-700">
                                    <span class="font-semibold">Pegs:</span> {{ $rig->pegSummary() }}
                                </p>
                            @endif
                            @if ($rig->notes)
                                <p class="mt-2 text-sm text-slate-700">{{ $rig->notes }}</p>
                            @endif

                            <div class="mt-4 flex flex-wrap items-end gap-3">
                                <a href="{{ route('tools.rigs.edit', $rig) }}" class="text-sm font-semibold text-sky-800 hover:underline">Edit</a>

                                <form method="POST" action="{{ route('tools.rigs.rename', $rig) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="rename-{{ $rig->id }}">Rig name</label>
                                    <input id="rename-{{ $rig->id }}" name="name" value="{{ $rig->displayName() }}" required maxlength="120"
                                           class="rounded-md border-2 border-slate-400 py-1.5 px-2 text-sm">
                                    <button class="text-sm font-semibold text-sky-800 hover:underline">Rename</button>
                                </form>

                                <form method="POST" action="{{ route('tools.rigs.duplicate', $rig) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <label class="sr-only" for="copy-{{ $rig->id }}">Name for the copy</label>
                                    <input id="copy-{{ $rig->id }}" name="name" value="{{ mb_substr('Copy of '.$rig->displayName(), 0, 120) }}" required maxlength="120"
                                           class="rounded-md border-2 border-slate-400 py-1.5 px-2 text-sm">
                                    <button class="text-sm font-semibold text-sky-800 hover:underline">Duplicate</button>
                                </form>

                                <form method="POST" action="{{ route('tools.rigs.destroy', $rig) }}" onsubmit="return confirm('Delete this rig?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-semibold text-red-800 hover:underline">Delete</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endauth

        <p class="text-sm text-slate-600">
            New to shotting patterns? Read the
            <a href="{{ route('tools.shot-guide') }}" class="font-semibold text-sky-800 hover:underline">shot guide</a>.
        </p>
    </div>
</x-app-layout>
