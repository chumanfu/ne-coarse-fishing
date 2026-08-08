<?php

use App\Models\Club;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $admin = User::query()
            ->where('email', 'admin@nefishing.test')
            ->first() ?? User::query()->first();

        if (! $admin) {
            return;
        }

        $club = Club::query()->updateOrCreate(
            ['slug' => 'birtley-rof-angling-club'],
            [
                'name' => 'Birtley ROF Angling Club',
                'url' => 'https://www.birtleyrofanglingclub.com/',
                'facebook_url' => null,
                'overview' => "Birtley ROF Angling Club (BROFAC) runs Ouston Springs Pond near Ouston / Pelton, Chester-le-Street. Membership is the main route onto the water (Feb–Feb via Clubmate), with limited day tickets only by club arrangement — not sold to the general public on the bank.",
                'town' => 'Birtley',
                'address' => 'Ouston Springs Farm, Pelton, Chester-le-Street, DH2 1JL',
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 170,
                'is_published' => true,
            ],
        );

        $venue = Venue::query()->updateOrCreate(
            ['slug' => 'ouston-springs-pond'],
            [
                'user_id' => $admin->id,
                'name' => 'Ouston Springs Pond',
                'overview' => "Club stillwater at Ouston Springs near Ouston / Pelton, managed by Birtley ROF Angling Club (BROFAC).\n\nFishing is from designated pegs only. Access is via the official car park above Ouston Springs Farm (past the Red Lion on Ouston Lane) — no dropping gear beyond the farm entrance, and the railway embankment is out of bounds.",
                'latitude' => 54.88364,
                'longitude' => -1.58249,
                'address' => 'Ouston Springs Farm, Pelton, Chester-le-Street, County Durham, DH2 1JL',
                'url' => 'https://www.birtleyrofanglingclub.com/',
                'facebook_url' => null,
                'what3words' => 'cackling.beep.hurry',
                'directions' => 'Access down past the Red Lion pub (Ouston Lane), then park only in the official car park above Ouston Springs Farm. Close car park and lake gates after use. Farm what3words: cackling.beep.hurry.',
                'day_ticket_info' => 'Not available for general public sale or on the bank. Club-arranged day tickets: Adults £10, Under 16 & Over 65 £5 — valid 7am–7pm only. Confirm with BROFAC before travelling.',
                'membership_info' => "Join via the club website / Clubmate. Adult £50 (+£1 online fee); Under 16 / Over 65 / Disabled £25 (+£1). Membership runs February to February. Contact Birtleyrofanglingclub@hotmail.com with problems.",
                'ticket_type' => 'club',
                'opening_times' => 'Subject to club rules. Day tickets (when issued) 7am–7pm. Members may night fish subject to rules; max 7 consecutive nights then 48 hours off.',
                'season_info' => 'Year-round subject to club and pond rules. AGM last Tuesday in January.',
                'tactics_guide' => "Club rules: max two rods; semi-barbed/barbed hooks only; groundbait up to 1kg in feeder or pole cup only (no balling in); floating hookbaits allowed but do not feed floaters; no spinning, live or deadbaiting; no meat/sweetcorn tins on the bank.",
                'is_complex' => false,
                'is_approved' => true,
                'manager_verified' => false,
            ],
        );

        Water::query()->updateOrCreate(
            [
                'venue_id' => $venue->id,
                'name' => 'Ouston Springs Pond',
            ],
            [
                'description' => 'Club stillwater at Ouston Springs — designated pegs only; railway embankment out of bounds.',
                'sort_order' => 1,
            ],
        );

        $venue->clubs()->syncWithoutDetaching([$club->id]);
    }

    public function down(): void
    {
        $venue = Venue::query()->where('slug', 'ouston-springs-pond')->first();

        if ($venue) {
            $venue->clubs()->detach();
            $venue->waters()->each(function (Water $water): void {
                $water->species()->detach();
                $water->delete();
            });
            $venue->delete();
        }

        Club::query()->where('slug', 'birtley-rof-angling-club')->delete();
    }
};
