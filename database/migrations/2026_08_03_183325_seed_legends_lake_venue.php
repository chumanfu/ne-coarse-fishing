<?php

use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePhoto;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Models\WaterPegPhoto;
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
            ['slug' => 'legends-lake'],
            [
                'user_id' => $admin->id,
                'name' => 'Legends Lake',
                'overview' => "Purpose-built 3.5 acre specimen lake at Carlton Village, Stockton-on-Tees — “Where Legends are Caught and Made”.\n\nAround 12 named pegs (11 bookable at once; The Rush offers pitches A or B). Double pegs for two anglers: Corn Crake (2), The Rush (4) and Stowaway (5).\n\nStock includes double-figure carp to around 32lb and wels catfish to around 47lb. Day/night sessions are booked online by peg; CCTV car park, toilets and on-site bait shop.",
                'latitude' => 54.5898436,
                'longitude' => -1.3886057,
                'address' => 'Letch Lane, Carlton Village, Stockton-on-Tees, TS21 1EB',
                'url' => 'https://legendslake.com/',
                'directions' => 'Legends Lake is on Letch Lane, Carlton Village, Stockton-on-Tees (TS21 1EB). Follow site signs to the CCTV car park; pegs are a short walk around the lake from there.',
                'day_ticket_info' => "Pre-book your peg online at legendslake.com — bookings cannot be made by phone or email.\n\n2026 prices: £32 for 24hrs (1 night); £60 for 2 anglers on a double peg (pegs 2, 4 or 5). Discounts for 4+ days apply automatically.\n\nWinter (1 Nov–28 Feb): £30 for 24hrs; £55 for 2 anglers on a double peg.\n\nAccess from 9.30am; fishing 10am until 9am the next day. Gates locked 8pm–7am (Apr–Sep) and dusk–8am (Oct–Mar) — contact the fishery if arriving after dusk.\n\nContact: 07307 355917 (9am–6pm) or contactlegendslake@gmail.com.",
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Thu–Sun 24hr sessions (changeover 9.30am). Closed Mon–Wed until spring 2027. Check legendslake.com for freezes and seasonal updates.',
                'season_info' => 'Specimen day/night fishery year-round subject to bookings, weather and site notices. Lake may freeze in winter — contact before travelling.',
                'tactics_guide' => "Named specimen pegs with islands, reed beds, lilies and depths from roughly 3.5ft out to 14ft in the deepest holes (notably towards The Bounty and Top Fin).\n\nDouble pegs (2, 4, 5) suit pairs; The Rush A/B lets you switch pitches for weather or a mid-session change.\n\nFollow Legends Lake bylaws and carp/catfish care rules — bailiffs check rigs. Full peg write-ups and photos are on the venue page and at legendslake.com/the-lake/.",
                'is_complex' => false,
                'is_approved' => true,
                'manager_verified' => false,
            ],
        );

        $water = Water::query()->updateOrCreate(
            [
                'venue_id' => $venue->id,
                'name' => 'Legends Lake',
            ],
            [
                'description' => '3.5 acre specimen lake with 12 named pegs (11 bookable). Deep margins, islands, reed beds and lilies; carp and wels catfish.',
                'peg_count' => 12,
                'depth_info' => 'Typically 4–9ft; deepest water around 14ft near The Bounty / Top Fin end',
                'sort_order' => 1,
            ],
        );

        $speciesIds = [];

        foreach (['carp', 'common-carp', 'mirror-carp', 'wels-catfish'] as $slug) {
            $id = Species::query()->where('slug', $slug)->value('id');

            if ($id) {
                $speciesIds[] = $id;
            }
        }

        if ($speciesIds !== []) {
            $water->species()->sync(array_values(array_unique($speciesIds)));
        }

        foreach ([
            ['images/venues/legends-lake/hero.jpg', 0],
            ['images/venues/legends-lake/map.png', 1],
        ] as [$path, $order]) {
            if (File::exists(public_path($path))) {
                VenuePhoto::query()->updateOrCreate(
                    [
                        'venue_id' => $venue->id,
                        'image_path' => $path,
                    ],
                    ['sort_order' => $order],
                );
            }
        }

        $pegs = [
            [
                'number' => '1',
                'name' => 'The Bounty',
                'latitude' => 54.58955,
                'longitude' => -1.38855,
                'sort_order' => 1,
                'photos' => ['1-the-bounty-1.jpg', '1-the-bounty-2.jpg'],
                'description' => "Peg closest to the car park.\n\nFeatures: Purpose-built curving high bank behind protects from westerly winds, with lots of space. A good winter peg that also draws fish in cooler summer spells thanks to the depth. Access to the deepest part of the lake on the left-hand side (around 14ft), plus an island, a sunken island with lily bed, and lots of margins (some with lilies). Generally deep water in front with roughly 4ft ledges around the margins; depths average about 5–9ft.\n\nPitching area: approx. 4m × 3m.",
            ],
            [
                'number' => '2',
                'name' => 'Corn Crake',
                'latitude' => 54.58962,
                'longitude' => -1.38818,
                'sort_order' => 2,
                'photos' => ['2-corn-crake-1.jpg', '2-corn-crake-2.jpg'],
                'description' => "Double peg — suitable for two anglers (select 2 guests when booking). More land space than any other peg, with a very large area for pitching two bivvies.\n\nFeatures: Well protected from wind for much of the year. Large central island to fish to, plus large areas of water with a maximum depth around 8ft. Long margins either side; the casting area sticks out slightly so margins are very accessible. Some lilies in shallower spots. Depths average around 6ft. Convenient and popular all year.\n\nPitching area: approx. 3.1m × 5.8m.",
            ],
            [
                'number' => '3',
                'name' => 'Solstice',
                'latitude' => 54.58988,
                'longitude' => -1.38800,
                'sort_order' => 3,
                'photos' => ['3-solstice-1.jpg', '3-solstice-2.jpg', '3-solstice-3.jpg'],
                'description' => "A commanding central peg, more than half surrounded by water.\n\nFeatures: Large casting angle with two islands to fish to. Sits near the narrower central point of the lake (about 40m across), so fish moving between larger bodies of water are channelled past — good for bites year-round. Depths generally around 5–6ft, with an 8ft channel along the opposite bank joining the two islands. Extensive fairly deep margins; reed bed on the right-hand side.\n\nPitching area: approx. 3.05m × 3.05m.",
            ],
            [
                'number' => '4a',
                'name' => 'The Rush A',
                'latitude' => 54.59012,
                'longitude' => -1.38812,
                'sort_order' => 4,
                'photos' => ['4a-the-rush-1.jpg', '4a-the-rush-2.jpg'],
                'description' => "Part of The Rush double booking (A or B for the price of one) — useful for prevailing weather or swapping mid-session. Also bookable for two anglers on separate pitches (you won’t sit right next to each other).\n\nFeatures: Two large islands close up with a narrow ~8ft-wide, ~6ft-deep channel between, plus extensive margin fishing and a small reedy pinnacle. Weed patches attract feeding fish. Close-up action; water averages around 5.5ft in front. Deepest water left of the island from 4a is about 8ft. Reeds and rushes attract fish year-round; a quiet, intense peg with sheltered views.\n\nPitching area 4a: approx. 3m × 2.9m.",
            ],
            [
                'number' => '4b',
                'name' => 'The Rush B',
                'latitude' => 54.59028,
                'longitude' => -1.38842,
                'sort_order' => 5,
                'photos' => ['4b-the-rush-1.jpg', '4b-the-rush-2.jpg'],
                'description' => "Second pitch of The Rush double booking (A or B). Same wild-card option as 4a — swap pitches for weather or variety on a multi-night trip, or book as a two-angler double peg.\n\nFeatures: Close-up island and margin fishing with weed, reeds and rushes. Fairly deep water averaging around 5.5ft in front of both pitches; sheltered from much of the lake’s weather with lovely views over surrounding fields.\n\nPitching area 4b: approx. 3.1m × 3.1m.",
            ],
            [
                'number' => '5',
                'name' => 'Stowaway',
                'latitude' => 54.59042,
                'longitude' => -1.38872,
                'sort_order' => 6,
                'photos' => ['5-stowaway-1.jpg', '5-stowaway-2.jpg', '5-stowaway-3.jpg'],
                'description' => "Double peg — suitable for two anglers (select 2 guests when booking). Furthest peg travelling anticlockwise from the car park; a cosy spot off the path, tucked into a dry hollow.\n\nFeatures: Large expanses of open water. Vast margin to the right; tiny island in front with a large island beyond. Reed beds left and right attract fish year-round. Deep water around the right-hand reeds (to about 11ft), extending up the margin then shallowing to ~6ft towards peg 6. A ~6ft trench runs the near margins; beyond the small island the lake is about 3.5–4ft with summer floating weed patches. Wonderful sense of isolation via its own short path.\n\nPitching area: approx. 6.5m × 3.3–4.3m.",
            ],
            [
                'number' => '6',
                'name' => 'The Stage',
                'latitude' => 54.59018,
                'longitude' => -1.38922,
                'sort_order' => 7,
                'photos' => ['6-the-stage-1.jpg', '6-the-stage-2.jpg'],
                'description' => "Furthest peg travelling clockwise from the car park. Cut back into the bank for a sweet bivvy pitch with a view over its own nature pond.\n\nFeatures: Very large rod set-up area and control of the largest area of water on the lake. Extensive left-hand margin with reed beds and lily patches; left margins 6–7ft with a ~4.5ft shelf about 6ft wide, deepening to ~8ft further along. Large central island (often weedy in front). Water in front ~6ft to halfway to the island, then ~4ft further out; right side 5–8ft. Popular peg with nice summer views.\n\nPitching area: approx. 3.3m × 3.6m.",
            ],
            [
                'number' => '7',
                'name' => 'Dream Weaver',
                'latitude' => 54.58995,
                'longitude' => -1.38935,
                'sort_order' => 8,
                'photos' => ['7-dream-weaver-1.jpg', '7-dream-weaver-2.jpg'],
                'description' => "Well sheltered peg — bivvy protected from westerlies by a high bank and hedge, with plenty of space.\n\nFeatures: Long margins both ways, a large central island, and a thin stretch of rushes with a deep channel against it. Generally shallower for the lake (~4ft over halfway to the island; ~6ft closer in). About 8ft in the left corner; right-hand rush island holds 6–7ft water close in. Lily beds in the largest area of sheltered water on average. Sunrise views ahead and a large nature pond to the left. Can be prolific in the right conditions.\n\nPitching area: approx. 4m × 4m.",
            ],
            [
                'number' => '8',
                'name' => 'Heart Wood',
                'latitude' => 54.58980,
                'longitude' => -1.38918,
                'sort_order' => 9,
                'photos' => ['8-heart-wood-1.jpg', '8-heart-wood-2.jpg'],
                'description' => "Spacious peg, well sheltered from westerlies by a large bank and hedge; bivvy pitch cut back into the bank.\n\nFeatures: Rush peninsula on the left sticks almost halfway out, with a deep hole toward the bank linking into an 8–9ft trench from the car-park end. Depths shallow to around 5ft toward the large island ahead and to the left. Near-bank margins about 4ft deep and 4ft wide. Excellent all-year peg where deeper water meets shallower water on a large scale.\n\nPitching area: approx. 3.2m × 4m.",
            ],
            [
                'number' => '9',
                'name' => 'The Ninth',
                'latitude' => 54.58968,
                'longitude' => -1.38898,
                'sort_order' => 10,
                'photos' => ['9-the-ninth-1.jpg', '9-the-ninth-2.jpg'],
                'description' => "“The Legion passed into Legend and probably passed here too.” An easy, good all-round peg with varying depths and two large feature islands — suitable for everyone.\n\nFeatures: Two large islands, reed beds and varied depths — generally deeper toward the angler, shallowing toward the islands. Lake narrows slightly to the right, helping channel fish end-to-end. Far-bank reeds often fishable with consideration for neighbours. Wind can affect fishing though the bivvy pitch is cut back for protection. Central peg with space either side; popular in autumn and summer.\n\nPitching area: approx. 3.5m × 3.4m.",
            ],
            [
                'number' => '10',
                'name' => 'Horizon',
                'latitude' => 54.58958,
                'longitude' => -1.38882,
                'sort_order' => 11,
                'photos' => ['10-horizon-1.jpg', '10-horizon-2.jpg'],
                'description' => "South-facing peg fairly close to the car park and amenities — convenient for getting a feel for the lake.\n\nFeatures: Slightly smaller swim than some pegs but generally quite deep (around 6ft). Large central island ahead and a very small island to the left. Lily pads in the near edge in summer. Well defended from north and east winds, so with the depth it is also a good winter peg.\n\nPitching area: approx. 3.1m × 3.1m.",
            ],
            [
                'number' => '11',
                'name' => 'Top Fin',
                'latitude' => 54.58948,
                'longitude' => -1.38872,
                'sort_order' => 12,
                'photos' => ['11-top-fin-1.jpg', '11-top-fin-2.jpg'],
                'description' => "First peg heading left from the car park. Sheltered from north and east winds; bivvy pitch cut back into the bank with a fishing platform jutting into the lake. Close to the car park and toilets.\n\nFeatures: Lots of deep water averaging 7–8ft in front — plumbing or a depth finder helps. Access to the lake’s deepest water on the right (~14ft). Wide margins either side with lily pads and steep drop-offs. Highly recommended in spring and winter when cold easterlies push fish here. Hidden sunken lily island plus a big central island of ornamental grasses; open water generally deep. Strong winter/spring peg that can produce all year.\n\nPitching area: approx. 3.1m × 3.1m.",
            ],
        ];

        foreach ($pegs as $pegData) {
            $photos = $pegData['photos'];
            unset($pegData['photos']);

            $peg = WaterPeg::query()->updateOrCreate(
                [
                    'water_id' => $water->id,
                    'number' => $pegData['number'],
                ],
                [
                    'created_by' => $admin->id,
                    'name' => $pegData['name'],
                    'description' => $pegData['description'],
                    'latitude' => $pegData['latitude'],
                    'longitude' => $pegData['longitude'],
                    'is_verified' => true,
                    'verified_by' => $admin->id,
                    'verified_at' => now(),
                    'sort_order' => $pegData['sort_order'],
                ],
            );

            foreach ($photos as $index => $filename) {
                $relative = 'images/venues/legends-lake/pegs/'.$filename;

                if (! File::exists(public_path($relative))) {
                    continue;
                }

                WaterPegPhoto::query()->updateOrCreate(
                    [
                        'water_peg_id' => $peg->id,
                        'image_path' => $relative,
                    ],
                    [
                        'sort_order' => $index,
                    ],
                );
            }
        }
    }

    public function down(): void
    {
        $venue = Venue::query()->where('slug', 'legends-lake')->first();

        if (! $venue) {
            return;
        }

        $venue->waters()->each(function (Water $water): void {
            $water->pegs()->each(function (WaterPeg $peg): void {
                $peg->photos()->delete();
                $peg->delete();
            });
            $water->species()->detach();
            $water->delete();
        });

        $venue->photos()->delete();
        $venue->delete();
    }
};
