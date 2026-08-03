<?php

use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePhoto;
use App\Models\Water;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

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

        $venue = Venue::query()->updateOrCreate(
            ['slug' => 'leazes-park-lake'],
            [
                'user_id' => $admin->id,
                'name' => 'Leazes Park Lake',
                'overview' => "City-centre mixed fishery of around 2 acres in Leazes Park, Newcastle upon Tyne, managed by Leazes Park Angling Association (LPAA).\n\nStocks include roach, rudd, perch, tench, bream, gudgeon, crucian carp, mirror carp and common carp. LPAA is affiliated to the Tyne Anglers Alliance, so members also gain access to shared TAA waters.",
                'latitude' => 54.9786,
                'longitude' => -1.6310,
                'address' => 'Leazes Park, Newcastle upon Tyne, NE1 4LP',
                'url' => 'https://leazesangling.com/',
                'directions' => 'Leazes Park sits just west of Newcastle city centre, between the Town Moor and St James’ Park. Enter via Barrack Road / Leazes Terrace and follow park paths to the lake. Limited street parking around the park — check local restrictions.',
                'day_ticket_info' => "Day tickets £6.50 per person, valid for the date specified from dawn until dusk only.\nBuy online via PayPal on leazesangling.com/day-tickets.html, or from Billy's Fishing Tackle (North Shields) and McDermotts (Ashington).\nRead and follow LPAA club rules before fishing. The committee may suspend fishing at any time.",
                'membership_info' => "Annual LPAA membership (postal 1 April–31 March) by post — application form on leazesangling.com.\n2026/27 fees: Senior £50, Concessionary OAP/disabled £30, Junior (16 or under) £5, 3rd rod £15.\nMembers can also fish Tyne Anglers Alliance waters while carrying their LPAA card.",
                'ticket_type' => 'mixed',
                'opening_times' => 'Day tickets: dawn until dusk on the purchased date. Check LPAA notices for temporary closures (e.g. carp spawning suspensions).',
                'season_info' => 'Stillwater coarse fishing year-round subject to club rules and seasonal suspensions.',
                'tactics_guide' => "Mixed silverfish and carp water in a busy city park — keep baiting tight and respect park users.\nPole, waggler and feeder with maggot, caster, worm and pellet all score.\nFollow LPAA barbless/hook and carp-care rules, especially during spawning closures.",
                'is_complex' => false,
                'is_approved' => true,
                'manager_verified' => false,
            ],
        );

        $water = Water::query()->updateOrCreate(
            [
                'venue_id' => $venue->id,
                'name' => 'Leazes Park Lake',
            ],
            [
                'description' => 'Mixed coarse lake of around 2 acres in Leazes Park.',
                'peg_count' => null,
                'depth_info' => 'Varied',
                'sort_order' => 1,
            ],
        );

        $speciesMap = [
            'carp' => ['carp', 'common-carp', 'mirror-carp'],
            'roach' => ['roach'],
            'rudd' => ['rudd'],
            'perch' => ['perch'],
            'tench' => ['tench'],
            'bream' => ['bream'],
            'gudgeon' => ['gudgeon'],
            'crucian' => ['crucian', 'crucian-carp'],
        ];

        $speciesIds = [];

        foreach ($speciesMap as $slugs) {
            foreach ($slugs as $slug) {
                $id = Species::query()->where('slug', $slug)->value('id');

                if ($id) {
                    $speciesIds[] = $id;
                    break;
                }
            }
        }

        if ($speciesIds !== []) {
            $water->species()->sync(array_values(array_unique($speciesIds)));
        }

        $relativePath = 'images/venues/leazes-park-lake.jpg';

        if (File::exists(public_path($relativePath))) {
            VenuePhoto::query()->updateOrCreate(
                [
                    'venue_id' => $venue->id,
                    'image_path' => $relativePath,
                ],
                [
                    'sort_order' => 0,
                ],
            );
        }
    }

    public function down(): void
    {
        $venue = Venue::query()->where('slug', 'leazes-park-lake')->first();

        if (! $venue) {
            return;
        }

        $venue->waters()->each(function (Water $water): void {
            $water->species()->detach();
            $water->delete();
        });

        $venue->photos()->delete();
        $venue->delete();
    }
};
