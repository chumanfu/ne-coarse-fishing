<li class="flex flex-col gap-1 px-4 py-2 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0 text-sm text-slate-900">
        <p>
            <span class="font-semibold">{{ $label }}</span>
            @if ($rig->catalogue === 'rw')
                <span class="text-slate-600">· {{ $rig->patternLabel() }}</span>
            @endif
            <span class="text-slate-600">· {{ $rig->depthLabel() }}</span>
        </p>
        @if ($rig->catalogue === 'rw' && $rig->notes)
            <p class="text-slate-600">{{ $rig->notes }}</p>
        @endif
    </div>
    <div class="flex shrink-0 items-center gap-3">
        <a href="{{ route('tools.rigs.edit', $rig) }}" class="text-sm font-semibold leading-5 text-sky-800 hover:underline">View</a>
        <button type="button" class="border-0 bg-transparent p-0 text-sm font-semibold leading-5 text-sky-800 hover:underline"
                onclick="openRigModal('copy-{{ $rig->id }}')">Duplicate</button>
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
                    <button type="button" class="text-sm font-semibold text-slate-700 hover:underline"
                            onclick="closeRigModal('copy-{{ $rig->id }}')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</li>
