<x-mail::message>
# You're invited to claim {{ $venue->name }}

Hi,

I'm Chris Mitchell, and I built **{{ config('app.name') }}** as a dedicated hub for pleasure anglers across the North East. After moving to Newcastle I found myself bouncing between outdated club sites, Facebook pages, and Google Maps just to find somewhere to fish — so I built one place that brings it together.

The site already includes:

- Club and venue directory listings for the region
- Local tackle shop details
- Session logging so anglers can share catch reports, tactics, and baits that are working on the bank

I've added a listing for **{{ $venue->name }}** and would love for someone from the fishery to claim and manage it — keeping details up to date, linking your waters, and connecting with local anglers.

<x-mail::button :url="route('venues.show', $venue)">
View your venue page
</x-mail::button>

**How to claim ownership**

1. [Register]({{ route('register') }}) for a free account (or [log in]({{ route('login') }}) if you already have one)
2. Open your [venue page]({{ route('venues.show', $venue) }})
3. Click **Claim ownership** and submit a short request

I'll review claims promptly and hand over management once verified.

If this isn't the right contact, feel free to forward it to whoever looks after the fishery — or reply and point me in the right direction.

Tight lines,<br>
Chris<br>
{{ config('app.name') }}
</x-mail::message>
