<x-filament-panels::page>
    @php
        /** @var \App\Models\MessageThread $thread */
        $thread = $this->getRecord();
    @endphp

    <style>
        .msg-thread { display: flex; flex-direction: column; gap: 1.25rem; }
        .msg-meta {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
            padding: 1.25rem 1.5rem;
            border-radius: 0.75rem;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
        }
        .msg-meta dt {
            margin: 0;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: rgba(255,255,255,.55);
        }
        .msg-meta dd {
            margin: 0.35rem 0 0;
            font-size: 0.9375rem;
            line-height: 1.45;
            color: #fff;
            word-break: break-word;
        }
        .msg-meta a { color: #7dd3fc; text-decoration: underline; text-underline-offset: 2px; }
        .msg-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 0.375rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .msg-badge--open { background: #064e3b; color: #d1fae5; border: 1px solid #059669; }
        .msg-badge--closed { background: rgba(255,255,255,.08); color: rgba(255,255,255,.75); border: 1px solid rgba(255,255,255,.18); }
        .msg-badge--source { background: rgba(14,165,233,.18); color: #bae6fd; border: 1px solid rgba(56,189,248,.35); }
        .msg-feed {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }
        .msg-bubble {
            border-radius: 0.75rem;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.03);
            padding: 1rem 1.15rem;
            max-width: min(44rem, 100%);
        }
        .msg-bubble--admin {
            align-self: flex-end;
            background: rgba(14,165,233,.14);
            border-color: rgba(56,189,248,.35);
        }
        .msg-bubble--participant {
            align-self: flex-start;
            background: rgba(255,255,255,.05);
        }
        .msg-bubble__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem 1rem;
            margin: 0 0 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: rgba(255,255,255,.55);
        }
        .msg-bubble__body {
            margin: 0;
            white-space: pre-line;
            color: rgba(255,255,255,.95);
            font-size: 0.9375rem;
            line-height: 1.55;
        }
        .msg-empty {
            padding: 1.25rem;
            border-radius: 0.75rem;
            border: 1px dashed rgba(255,255,255,.18);
            color: rgba(255,255,255,.55);
            font-size: 0.875rem;
        }
    </style>

    <div class="msg-thread">
        <div class="msg-meta">
            <div>
                <dt>From</dt>
                <dd>
                    {{ $thread->displayName() }}
                    <br>
                    <a href="mailto:{{ $thread->contact_email }}">{{ $thread->contact_email }}</a>
                </dd>
            </div>
            <div>
                <dt>Status</dt>
                <dd>
                    <span @class(['msg-badge', 'msg-badge--open' => ! $thread->isClosed(), 'msg-badge--closed' => $thread->isClosed()])>
                        {{ $thread->status }}
                    </span>
                    <span class="msg-badge msg-badge--source" style="margin-left: 0.4rem;">
                        {{ $thread->source === 'admin' ? 'Started by admin' : 'Contact form' }}
                    </span>
                </dd>
            </div>
            @if ($thread->user)
                <div>
                    <dt>Registered user</dt>
                    <dd>{{ $thread->user->name }}</dd>
                </div>
            @endif
            <div>
                <dt>Latest activity</dt>
                <dd>{{ $thread->last_message_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?: '—' }}</dd>
            </div>
        </div>

        <div class="msg-feed">
            @forelse ($thread->messages as $message)
                <article @class([
                    'msg-bubble',
                    'msg-bubble--admin' => $message->is_from_admin,
                    'msg-bubble--participant' => ! $message->is_from_admin,
                ])>
                    <div class="msg-bubble__meta">
                        <span>{{ $message->senderLabel() }}{{ $message->is_from_admin ? ' · Admin' : '' }}</span>
                        <span>{{ $message->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</span>
                    </div>
                    <p class="msg-bubble__body">{{ $message->body }}</p>
                </article>
            @empty
                <p class="msg-empty">No messages in this conversation yet.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
