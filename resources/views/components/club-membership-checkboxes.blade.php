@props([
    'clubs',
    'selected' => [],
    'hint' => 'Select any North East clubs you belong to. You can change this later in your profile.',
])

@php
    $selectedIds = collect(old('club_ids', $selected))->map(fn ($id) => (int) $id)->all();
@endphp

<div>
    <x-input-label value="{{ __('Club memberships') }}" />
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $hint }}</p>

    <div class="mt-3 max-h-56 overflow-y-auto rounded-md border-2 border-slate-300 bg-white p-3 space-y-2 dark:border-slate-600 dark:bg-slate-950">
        @forelse ($clubs as $club)
            <label class="flex items-start gap-2 text-sm text-slate-800 cursor-pointer dark:text-slate-100">
                <input
                    type="checkbox"
                    name="club_ids[]"
                    value="{{ $club->id }}"
                    class="mt-0.5 rounded border-slate-400 text-sky-700 focus:ring-sky-700 dark:border-slate-500 dark:bg-slate-900"
                    @checked(in_array($club->id, $selectedIds, true))
                >
                <span>
                    <span class="font-semibold">{{ $club->name }}</span>
                    @if ($club->town)
                        <span class="text-slate-500 dark:text-slate-400"> · {{ $club->town }}</span>
                    @endif
                </span>
            </label>
        @empty
            <p class="text-sm text-slate-600 dark:text-slate-300">Club directory is empty for now.</p>
        @endforelse
    </div>

    <x-input-error :messages="$errors->get('club_ids')" class="mt-2" />
    <x-input-error :messages="$errors->get('club_ids.*')" class="mt-2" />
</div>
