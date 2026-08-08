<?php

use App\Models\Venue;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $venue = Venue::query()->where('slug', 'wingate-ponds')->first()
            ?? Venue::query()->where('slug', 'eden-meadows-fishery')->first();

        if (! $venue) {
            return;
        }

        $venue->update([
            'name' => 'Eden Meadows Fishery',
            'slug' => 'eden-meadows-fishery',
            'overview' => "Day-ticket three-lake complex on Wingate Road, County Durham — locally also known as Wingate Ponds.\n\nMeadow Lake is the main pleasure water (~30 pegs); Cherry Tree and Acer are smaller match-style lakes stocked with carp and tench. On-site parking with wheelchair access to pegs; toilets and a small shop historically available.",
            'latitude' => 54.7205,
            'longitude' => -1.3920,
            'address' => 'Wingate Road, Wingate / Station Town, County Durham, TS28 5LZ',
            'url' => 'http://www.edenmeadows.webeden.co.uk/',
            'directions' => 'On Wingate Road between Station Town and Trimdon Station (TS28 5LZ). Drive-down access for disabled anglers when permitted.',
            'day_ticket_info' => "Historically around £7 adult, £5 disabled/junior, £4 evening — closed Mondays except bank holidays.\nPhone often listed as 07999 955099. Confirm current opening and prices before travelling (venue status can change).",
            'ticket_type' => 'day_ticket',
            'opening_times' => 'Typically Tue–Sun; closed Mondays (except bank holidays) — verify before visiting.',
            'season_info' => 'Most of the year when open.',
            'tactics_guide' => 'Pole across to far banks on Acer/Cherry Tree for carp and tench; Meadow suits pleasure bags on pellet, corn and maggot including occasional double-figure carp.',
            'is_complex' => true,
            'is_approved' => true,
        ]);
    }

    public function down(): void
    {
        $venue = Venue::query()->where('slug', 'eden-meadows-fishery')->first();

        if (! $venue) {
            return;
        }

        $venue->update([
            'name' => 'Wingate Ponds',
            'slug' => 'wingate-ponds',
            'overview' => "Day-ticket three-lake complex on Wingate Road, County Durham — also known as Eden Meadows Fishery.\n\nMeadow Lake is the main pleasure water (~30 pegs); Cherry Tree and Acer are smaller match-style lakes stocked with carp and tench. On-site parking with wheelchair access to pegs; toilets and a small shop historically available.",
            'url' => null,
            'day_ticket_info' => 'Historically around £7 adult, £5 disabled/junior, £4 evening — closed Mondays except bank holidays. Confirm current opening and prices before travelling (venue status can change).',
        ]);
    }
};
