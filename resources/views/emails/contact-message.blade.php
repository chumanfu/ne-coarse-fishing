<x-mail::message>
# New contact message

**From:** {{ $name }} &lt;{{ $email }}&gt;

**Subject:** {{ $subjectLine }}

{{ $messageBody }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
