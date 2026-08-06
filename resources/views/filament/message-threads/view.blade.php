@php
    /** @var \App\Models\MessageThread $thread */
    $thread = $this->getRecord();
@endphp

<div class="space-y-6">
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="font-semibold text-gray-500 dark:text-gray-400">From</dt>
                <dd class="mt-1 text-gray-950 dark:text-white">{{ $thread->displayName() }} &lt;{{ $thread->contact_email }}&gt;</dd>
            </div>
            <div>
                <dt class="font-semibold text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1 text-gray-950 dark:text-white capitalize">{{ $thread->status }} · {{ $thread->source === 'admin' ? 'Started by admin' : 'Contact form' }}</dd>
            </div>
            @if ($thread->user)
                <div>
                    <dt class="font-semibold text-gray-500 dark:text-gray-400">Registered user</dt>
                    <dd class="mt-1 text-gray-950 dark:text-white">{{ $thread->user->name }} ({{ $thread->user->email }})</dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="space-y-4">
        @foreach ($thread->messages as $message)
            <div @class([
                'rounded-xl border p-4',
                'border-sky-300 bg-sky-50 dark:border-sky-700 dark:bg-sky-950/40' => $message->is_from_admin,
                'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900' => ! $message->is_from_admin,
            ])>
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span>{{ $message->senderLabel() }}{{ $message->is_from_admin ? ' · Admin' : '' }}</span>
                    <span>{{ $message->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</span>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm text-gray-900 dark:text-gray-100">{{ $message->body }}</p>
            </div>
        @endforeach
    </div>
</div>
