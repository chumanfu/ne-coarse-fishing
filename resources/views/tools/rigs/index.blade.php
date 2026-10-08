<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Rigs</h1>
        <p class="text-slate-600 mt-1">Work out the shot a float needs, move it on the line, and keep the rig for the venues you fish.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">
        <a href="{{ route('tools.rigs.create') }}" class="inline-flex items-center px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">
            Create New Rig
        </a>

        @guest
            <p class="text-sm text-slate-700">
                <a href="{{ route('register') }}" class="font-semibold text-sky-800 hover:underline">Create a free account</a>
                or <a href="{{ route('login') }}" class="font-semibold text-sky-800 hover:underline">log in</a>
                to see the rigs you have saved.
            </p>
        @endguest

        @auth
            <section class="space-y-3" x-data="{ open: false }">
                <button type="button" class="flex w-full items-center gap-2 border-0 bg-transparent p-0 text-left" @click="open = !open" :aria-expanded="open">
                    <span class="inline-flex w-8 shrink-0 items-center justify-center text-2xl leading-none text-slate-500" x-text="open ? '▾' : '▸'" aria-hidden="true"></span>
                    <h2 class="text-lg font-bold text-slate-900">My Rigs</h2>
                </button>
                <div x-show="open" x-cloak>
            @if ($rigs->isEmpty())
                <p class="text-sm text-slate-700">You have no rigs of your own yet. Duplicate a rig below, or create a new one.</p>
            @else
                <ul class="divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-300 bg-white">
                    @foreach ($rigs as $rig)
                        <li class="px-4 py-3">
                            <div class="min-w-0">
                                <h2 class="font-semibold text-slate-900">{{ $rig->displayName() }}</h2>
                                <p class="text-sm text-slate-600">
                                    {{ $rig->float_name }} · {{ $rig->float_size }} · {{ $rig->floatTypeLabel() }} · {{ $rig->depthLabel() }} · {{ $rig->patternLabel() }}
                                </p>
                                @if ($rig->venues->isNotEmpty() || $rig->pegSummary() !== '')
                                    <p class="text-sm text-slate-600">
                                        @if ($rig->venues->isNotEmpty())
                                            <span class="font-semibold text-slate-700">Venues:</span> {{ $rig->venueSummary() }}
                                        @endif
                                        @if ($rig->pegSummary() !== '')
                                            @if ($rig->venues->isNotEmpty())
                                                <span class="text-slate-400">·</span>
                                            @endif
                                            <span class="font-semibold text-slate-700">Pegs:</span> {{ $rig->pegSummary() }}
                                        @endif
                                    </p>
                                @endif
                                @if ($rig->notes)
                                    <p class="text-sm text-slate-700">{{ $rig->notes }}</p>
                                @endif
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-3">
                                <a href="{{ route('tools.rigs.edit', $rig) }}" class="text-sm font-semibold leading-5 text-sky-800 hover:underline">Edit</a>
                                <button type="button" class="border-0 bg-transparent p-0 text-sm font-semibold leading-5 text-sky-800 hover:underline"
                                        onclick="openRigModal('notes-{{ $rig->id }}')">
                                    {{ $rig->notes ? 'Edit notes' : 'Add notes' }}
                                </button>
                                <button type="button" class="border-0 bg-transparent p-0 text-sm font-semibold leading-5 text-sky-800 hover:underline"
                                        onclick="openRigModal('rename-{{ $rig->id }}')">
                                    Rename
                                </button>
                                <button type="button" class="border-0 bg-transparent p-0 text-sm font-semibold leading-5 text-sky-800 hover:underline"
                                        onclick="openRigModal('copy-{{ $rig->id }}')">
                                    Duplicate
                                </button>
                                <form method="POST" action="{{ route('tools.rigs.destroy', $rig) }}" class="inline-flex" onsubmit="return confirm('Delete this rig?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="border-0 bg-transparent p-0 text-sm font-semibold leading-5 text-red-800 hover:underline">Delete</button>
                                </form>
                            </div>

                            <div id="notes-{{ $rig->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="notes-title-{{ $rig->id }}" data-rig-modal>
                                <button type="button" class="absolute inset-0 bg-slate-900/40" aria-label="Close" onclick="closeRigModal('notes-{{ $rig->id }}')"></button>
                                <form method="POST" action="{{ route('tools.rigs.notes', $rig) }}" class="relative w-full max-w-lg space-y-3 rounded-xl border-2 border-slate-300 bg-white p-5">
                                    @csrf
                                    @method('PATCH')
                                    <h3 id="notes-title-{{ $rig->id }}" class="text-lg font-bold text-slate-900">Notes for {{ $rig->displayName() }}</h3>
                                    <label for="notes-text-{{ $rig->id }}" class="block text-sm font-semibold">Notes</label>
                                    <textarea id="notes-text-{{ $rig->id }}" name="notes" rows="4" maxlength="2000"
                                              class="w-full rounded-md border-2 border-slate-400 py-2 px-3 text-sm">{{ $rig->notes }}</textarea>
                                    <div class="flex items-center gap-3">
                                        <button class="px-4 py-2 rounded-md bg-sky-800 text-white font-bold">Save notes</button>
                                        <button type="button" class="border-0 bg-transparent p-0 text-sm font-semibold text-slate-700 hover:underline"
                                                onclick="closeRigModal('notes-{{ $rig->id }}')">Cancel</button>
                                    </div>
                                </form>
                            </div>

                            <div id="rename-{{ $rig->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="rename-title-{{ $rig->id }}" data-rig-modal>
                                <button type="button" class="absolute inset-0 bg-slate-900/40" aria-label="Close" onclick="closeRigModal('rename-{{ $rig->id }}')"></button>
                                <form method="POST" action="{{ route('tools.rigs.rename', $rig) }}" class="relative w-full max-w-lg space-y-3 rounded-xl border-2 border-slate-300 bg-white p-5">
                                    @csrf
                                    @method('PATCH')
                                    <h3 id="rename-title-{{ $rig->id }}" class="text-lg font-bold text-slate-900">Rename this rig</h3>
                                    <label for="rename-name-{{ $rig->id }}" class="sr-only">Rig name</label>
                                    <input id="rename-name-{{ $rig->id }}" name="name" required maxlength="120"
                                           value="{{ $rig->displayName() }}"
                                           class="w-full rounded-md border-2 border-slate-400 py-2 px-3 text-sm">
                                    <div class="flex items-center gap-3">
                                        <button class="px-4 py-2 rounded-md bg-sky-800 text-white font-bold">Rename</button>
                                        <button type="button" class="border-0 bg-transparent p-0 text-sm font-semibold text-slate-700 hover:underline"
                                                onclick="closeRigModal('rename-{{ $rig->id }}')">Cancel</button>
                                    </div>
                                </form>
                            </div>

                            <div id="copy-{{ $rig->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="copy-title-{{ $rig->id }}" data-rig-modal>
                                <button type="button" class="absolute inset-0 bg-slate-900/40" aria-label="Close" onclick="closeRigModal('copy-{{ $rig->id }}')"></button>
                                <form method="POST" action="{{ route('tools.rigs.duplicate', $rig) }}" class="relative w-full max-w-lg space-y-3 rounded-xl border-2 border-slate-300 bg-white p-5">
                                    @csrf
                                    <h3 id="copy-title-{{ $rig->id }}" class="text-lg font-bold text-slate-900">Name for your copy</h3>
                                    <label for="copy-name-{{ $rig->id }}" class="sr-only">Name for your copy</label>
                                    <input id="copy-name-{{ $rig->id }}" name="name" required maxlength="120"
                                           value="{{ mb_substr('Copy of '.$rig->displayName(), 0, 120) }}"
                                           class="w-full rounded-md border-2 border-slate-400 py-2 px-3 text-sm">
                                    <div class="flex items-center gap-3">
                                        <button class="px-4 py-2 rounded-md bg-sky-800 text-white font-bold">Duplicate</button>
                                        <button type="button" class="border-0 bg-transparent p-0 text-sm font-semibold text-slate-700 hover:underline"
                                                onclick="closeRigModal('copy-{{ $rig->id }}')">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
                </div>
            </section>

            <section class="space-y-3" x-data="{ open: false }">
                <button type="button" class="flex w-full items-center gap-2 border-0 bg-transparent p-0 text-left" @click="open = !open" :aria-expanded="open">
                    <span class="inline-flex w-8 shrink-0 items-center justify-center text-2xl leading-none text-slate-500" x-text="open ? '▾' : '▸'" aria-hidden="true"></span>
                    <h2 class="text-lg font-bold text-slate-900">RW Floats</h2>
                </button>
                <div x-show="open" x-cloak class="space-y-3">
                    <p class="text-sm text-slate-600">RW's own shotting. The float name and type are part of the rig. Duplicate one to make a copy of your own.</p>
                    <p class="text-sm">
                        <a href="https://rwfloats.co.uk/" target="_blank" rel="noopener noreferrer" class="text-sky-800 font-semibold hover:underline">https://rwfloats.co.uk/</a>
                    </p>

                    @foreach ($rwRigs as $floatName => $floatRigs)
                        <div x-data="{ open: false }">
                            <button type="button" class="flex items-center gap-2 border-0 bg-transparent p-0 text-left" @click="open = !open" :aria-expanded="open">
                                <span class="inline-flex w-8 shrink-0 items-center justify-center text-2xl leading-none text-slate-500" x-text="open ? '▾' : '▸'" aria-hidden="true"></span>
                                <h3 class="font-semibold text-slate-900">{{ $floatName }}</h3>
                            </button>
                            <div x-show="open" x-cloak>
                                <p class="mt-1 text-sm text-slate-600">{{ $floatRigs->first()->floatTypeLabel() }}</p>
                                <ul class="mt-2 divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-300 bg-white">
                                    @foreach ($floatRigs as $rig)
                                        @include('tools.rigs.partials.template-row', ['rig' => $rig, 'label' => $rig->float_size])
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="space-y-3" x-data="{ open: false }">
                <button type="button" class="flex w-full items-center gap-2 border-0 bg-transparent p-0 text-left" @click="open = !open" :aria-expanded="open">
                    <span class="inline-flex w-8 shrink-0 items-center justify-center text-2xl leading-none text-slate-500" x-text="open ? '▾' : '▸'" aria-hidden="true"></span>
                    <h2 class="text-lg font-bold text-slate-900">Standard Rigs</h2>
                </button>
                <div x-show="open" x-cloak class="space-y-3">
                    <p class="text-sm text-slate-600">Templates for the usual pole floats. Duplicate one to make a copy of your own. The original cannot be changed or deleted.</p>

                    @foreach ($standardRigs as $size => $sizeRigs)
                        <div x-data="{ open: false }">
                            <button type="button" class="flex items-center gap-2 border-0 bg-transparent p-0 text-left" @click="open = !open" :aria-expanded="open">
                                <span class="inline-flex w-8 shrink-0 items-center justify-center text-2xl leading-none text-slate-500" x-text="open ? '▾' : '▸'" aria-hidden="true"></span>
                                <h3 class="font-semibold text-slate-900">{{ $size }}</h3>
                            </button>
                            <div x-show="open" x-cloak>
                                <p class="mt-1 text-sm text-slate-600">{{ \App\Support\SystemRigs::blurb($size) }}</p>
                                <ul class="mt-2 divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-300 bg-white">
                                    @foreach ($sizeRigs as $rig)
                                        @include('tools.rigs.partials.template-row', ['rig' => $rig, 'label' => $rig->displayName()])
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endauth

        <p class="text-sm text-slate-600">
            New to shotting patterns? Read the
            <a href="{{ route('tools.shot-guide') }}" class="font-semibold text-sky-800 hover:underline">shot guide</a>.
        </p>
    </div>

    <script>
        function openRigModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.querySelector('textarea, input:not([type="hidden"])')?.focus();
        }

        function closeRigModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('[data-rig-modal]:not(.hidden)').forEach((modal) => modal.classList.add('hidden'));
        });
    </script>
</x-app-layout>
