<x-mail::message>
# New contact message

**From:** {{ $name }} &lt;{{ $email }}&gt;

**Subject:** {{ $subjectLine }}

{{ $messageBody }}

@if ($thread)
<x-mail::button :url="url('/admin/message-threads/'.$thread->id)">
Open in admin messages
</x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
