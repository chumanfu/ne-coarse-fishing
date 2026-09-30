<x-mail::message>
# Ownership claim submitted

**Claimant:** {{ $claimant->name }} &lt;{{ $claimant->email }}&gt;

**Listing:** {{ $listingName }} ({{ $listingKind }})

@if (filled($message))
{{ $message }}
@else
No message was included with this claim.
@endif

<x-mail::button :url="$reviewUrl">
Review claims
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
