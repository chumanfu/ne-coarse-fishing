<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-sky-800 mb-1"><a href="{{ route('messages.index') }}" class="hover:underline">&larr; All messages</a></p>
                <h1 class="text-2xl font-bold text-slate-900">{{ $thread->subject }}</h1>
                <p class="text-slate-600 mt-1 capitalize">{{ $thread->status }} conversation</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">
        @foreach ($thread->messages as $message)
            <div @class([
                'rounded-xl border-2 p-4',
                'border-sky-600 bg-sky-50' => $message->is_from_admin,
                'border-slate-300 bg-white' => ! $message->is_from_admin,
            ])>
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <span>{{ $message->senderLabel() }}{{ $message->is_from_admin ? ' · Site team' : '' }}</span>
                    <span>{{ $message->created_at->format('d M Y H:i') }}</span>
                </div>
                <p class="mt-3 whitespace-pre-line text-slate-900">{{ $message->body }}</p>
            </div>
        @endforeach

        @can('reply', $thread)
            <form method="POST" action="{{ route('messages.reply', $thread) }}" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-3">
                @csrf
                <label for="body" class="block text-sm font-semibold">Your reply</label>
                <textarea id="body" name="body" rows="5" required maxlength="5000" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror
                <button type="submit" class="px-4 py-2 rounded-md bg-sky-700 text-white font-semibold text-sm">Send reply</button>
            </form>
        @else
            <p class="text-sm text-slate-600">This conversation is closed.</p>
        @endcan
    </div>
</x-app-layout>
