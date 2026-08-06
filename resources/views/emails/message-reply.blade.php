<x-mail::message>
@if ($forAdmin)
# New reply in your messages

**From:** {{ $thread->displayName() }} &lt;{{ $thread->contact_email }}&gt;

**Subject:** {{ $thread->subject }}

{{ $message->body }}

<x-mail::button :url="url('/admin/message-threads/'.$thread->id)">
Reply in admin
</x-mail::button>
@else
# New message from {{ config('app.name') }}

**Subject:** {{ $thread->subject }}

{{ $message->body }}

@if ($thread->user_id)
<x-mail::button :url="route('messages.show', $thread)">
View conversation
</x-mail::button>
@else
You can reply by email, or register with this address to continue the conversation on the site.
@endif
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
