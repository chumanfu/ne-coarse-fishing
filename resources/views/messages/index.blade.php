<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Messages</h1>
                <p class="text-slate-600 mt-1">Conversations with the site team.</p>
            </div>
            <a href="{{ route('contact.create') }}" class="inline-flex px-4 py-2 rounded-md bg-sky-700 text-white font-semibold text-sm">New message</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-3">
        @forelse ($threads as $thread)
            <a href="{{ route('messages.show', $thread) }}" class="block bg-white border-2 rounded-xl p-4 hover:border-sky-700 transition {{ $thread->isUnreadForParticipant() ? 'border-amber-500' : 'border-slate-300' }}">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h2 class="font-bold text-slate-900">{{ $thread->subject }}</h2>
                    <span class="text-xs font-semibold text-slate-500">{{ $thread->last_message_at?->format('d M Y H:i') }}</span>
                </div>
                @if ($thread->latestMessage)
                    <p class="text-sm text-slate-700 mt-2 line-clamp-2">{{ $thread->latestMessage->body }}</p>
                @endif
                <p class="text-xs text-slate-500 mt-2 capitalize">{{ $thread->status }}@if ($thread->isUnreadForParticipant()) · New reply@endif</p>
            </a>
        @empty
            <p class="text-slate-600">No messages yet. Use the contact form if you need to get in touch.</p>
        @endforelse

        <div class="pt-4">{{ $threads->links() }}</div>
    </div>
</x-app-layout>
