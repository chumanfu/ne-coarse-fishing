<x-mail::message>
# New user signed up

**Name:** {{ $user->name }}

**Email:** {{ $user->email }}

<x-mail::button :url="$reviewUrl">
Open in admin
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
