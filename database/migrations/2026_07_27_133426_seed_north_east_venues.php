<?php

use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $speciesSlugs = [];

    public function up(): void
    {
        $admin = User::query()
            ->where('email', 'admin@nefishing.test')
            ->first() ?? User::query()->first();

        if (! $admin) {
            return;
        }

        $this->speciesSlugs = Species::query()
            ->pluck('id', 'slug')
            ->all();

        foreach ($this->venues() as $venueData) {
            $waters = $venueData['waters'];
            unset($venueData['waters']);

            $venue = Venue::query()->updateOrCreate(
                ['slug' => $venueData['slug']],
                [
                    ...$venueData,
                    'user_id' => $admin->id,
                    'is_approved' => true,
                    'manager_verified' => false,
                ]
            );

            foreach ($waters as $index => $waterData) {
                $species = $waterData['species'] ?? [];
                unset($waterData['species']);

                $water = Water::query()->updateOrCreate(
                    [
                        'venue_id' => $venue->id,
                        'name' => $waterData['name'],
                    ],
                    [
                        ...$waterData,
                        'sort_order' => $waterData['sort_order'] ?? ($index + 1),
                    ]
                );

                if ($species !== []) {
                    $water->species()->sync($this->speciesIds($species));
                }
            }
        }
    }

    public function down(): void
    {
        $slugs = array_column($this->venues(), 'slug');

        Venue::query()
            ->whereIn('slug', $slugs)
            ->each(function (Venue $venue): void {
                $venue->waters()->each(function (Water $water): void {
                    $water->species()->detach();
                    $water->delete();
                });

                $venue->delete();
            });
    }

    /**
     * @param  list<string>  $aliases
     * @return list<int>
     */
    private function speciesIds(array $aliases): array
    {
        $map = [
            'carp' => ['carp', 'common-carp', 'mirror-carp'],
            'roach' => ['roach'],
            'rudd' => ['rudd'],
            'tench' => ['tench'],
            'bream' => ['bream'],
            'perch' => ['perch'],
            'pike' => ['pike'],
            'chub' => ['chub'],
            'crucian' => ['crucian', 'crucian-carp'],
            'ide' => ['ide'],
            'orfe' => ['orfe'],
            'gudgeon' => ['gudgeon'],
            'f1' => ['f1'],
            'barbel' => ['barbel'],
            'dace' => ['dace'],
            'eel' => ['european-eel'],
            'brown-trout' => ['brown-trout'],
            'rainbow-trout' => ['rainbow-trout'],
            'sea-trout' => ['sea-trout'],
            'salmon' => ['atlantic-salmon'],
            'flounder' => [],
        ];

        $ids = [];

        foreach ($aliases as $alias) {
            foreach ($map[$alias] ?? [Str::slug($alias)] as $slug) {
                if (isset($this->speciesSlugs[$slug])) {
                    $ids[] = $this->speciesSlugs[$slug];
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function venues(): array
    {
        return [
            [
                'slug' => 'killingworth-lakes',
                'name' => 'Killingworth Lakes',
                'overview' => "Two well-stocked stillwaters on the northern edge of Newcastle, managed by the Tyne Anglers Alliance. The large lake in particular has strong natural recruitment of roach alongside carp, bream, tench, perch, crucians, rudd and pike.\n\nWheelchair access runs along the full southern bank of the larger lake.",
                'latitude' => 55.03154,
                'longitude' => -1.56987,
                'address' => 'Killingworth Lake, Killingworth, Newcastle upon Tyne NE12 6SA',
                'directions' => 'Access via Killingworth Country Park. Day tickets from Killingworth Leisure Centre or White Swan Centre.',
                'day_ticket_info' => 'Day tickets available from Killingworth Leisure Centre and White Swan Centre. Junior, senior and OAP rates apply.',
                'membership_info' => 'Also available through Tyne Anglers Alliance affiliated clubs.',
                'ticket_type' => 'mixed',
                'opening_times' => 'Typically 08:00–20:00 for day ticket anglers; check TAA notices.',
                'season_info' => 'Stillwater coarse fishing year-round. Pike fishing only 1 October to 30 April inclusive.',
                'tactics_guide' => "Feeder and waggler with maggot and pellet work well for silvers.\nPike fishing requires wire traces, unhooking mat and minimum 40\" landing net.\nNo coarse dead baits for pike.",
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Large Lake',
                        'description' => 'Main water with strong roach stocks, carp and mixed silvers.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['carp', 'roach', 'rudd', 'tench', 'bream', 'perch', 'crucian', 'pike'],
                    ],
                    [
                        'name' => 'Small Lake',
                        'description' => 'Smaller companion lake with mixed coarse species.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['carp', 'roach', 'rudd', 'tench', 'bream', 'perch', 'crucian', 'pike'],
                    ],
                ],
            ],
            [
                'slug' => 'eden-grange',
                'name' => 'Eden Grange',
                'overview' => "Commercial coarse fishery near Shildon with three well-stocked ponds in 12 acres of County Durham countryside. On-site café, tackle shop and disabled access.\n\nPopular for pleasure and match fishing with bags of 150lb+ possible on the match water.",
                'latitude' => 54.6288,
                'longitude' => -1.6536,
                'address' => 'Eden Grange Fishery, off Dale Road, Shildon, County Durham DL4 2QD',
                'directions' => 'From A1(M) J58 take A68 towards West Auckland, then A6072 towards Shildon. Turn right onto Dale Road and follow the lane to the fishery car park.',
                'day_ticket_info' => 'Day tickets around £12–£16 depending on lake and season. Book online where possible.',
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Check fishery website for current pleasure fishing hours.',
                'season_info' => 'Open year-round on stillwaters.',
                'tactics_guide' => "Hackworth Lake: maggot, pellet and worm; shallow in summer.\nMatch Pond: margins and far bank in summer with maggot, pellet, corn and bread.\nRunswater: mixed carp and silverfish tactics.",
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Hackworth Lake',
                        'description' => '24-peg match and pleasure lake averaging 4ft depth.',
                        'peg_count' => 24,
                        'depth_info' => 'Around 4ft',
                        'species' => ['carp', 'tench', 'chub', 'ide', 'roach', 'rudd', 'perch', 'crucian'],
                    ],
                    [
                        'name' => 'Match Pond',
                        'description' => 'Snake lake with 12 pegs, ideal for margin fishing in summer.',
                        'peg_count' => 12,
                        'depth_info' => '3–4ft',
                        'species' => ['carp', 'chub', 'ide', 'f1', 'rudd'],
                    ],
                    [
                        'name' => 'Runswater',
                        'description' => 'Mixed specimen and match lake with carp to 30lb and good silverfish sport.',
                        'peg_count' => 14,
                        'depth_info' => 'Mixed depths',
                        'species' => ['carp', 'tench', 'chub', 'ide', 'roach', 'rudd', 'perch', 'f1', 'crucian'],
                    ],
                ],
            ],
            [
                'slug' => 'hebron-lake',
                'name' => 'Hebron Lake',
                'overview' => 'Sheltered woodland coarse fishery north of Morpeth with 18 pegs including disabled-access platforms. Ideal for novices and experienced anglers alike.',
                'latitude' => 55.1540,
                'longitude' => -1.6900,
                'address' => 'Hebron Lakes Coarse Fishery, Hebron, Morpeth, Northumberland NE61 3DZ',
                'directions' => 'From A1 north of Morpeth follow signs for Hebron village. Turn left at the T-junction, continue 1 mile and follow the Hebron Lakes sign on the right through woodland to the car park.',
                'day_ticket_info' => 'Adult day ticket approx. £7, junior £4.50. Book online in advance.',
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Book sessions online; peg numbers limited to 18.',
                'season_info' => 'Stillwater fishing year-round.',
                'tactics_guide' => 'Pole, waggler and feeder with maggot and caster. Barbless hooks only. One rod unless extra rod fee paid.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Hebron Lake',
                        'description' => 'Tree-lined mixed coarse lake with 18 pegs.',
                        'peg_count' => 18,
                        'depth_info' => 'Varied',
                        'species' => ['bream', 'roach', 'perch', 'crucian', 'rudd', 'gudgeon'],
                    ],
                ],
            ],
            [
                'slug' => 'qe-ii',
                'name' => 'QE II',
                'overview' => "40-acre lake in QEII Country Park, Ashington, run by Wansbeck and Cramlington Angling Club. Big carp, bream, pike, roach, rudd, tench and perch in an urban country-park setting.\n\nShares the water with watersports users — pick a quiet bank away from sailing activity.",
                'latitude' => 55.19629,
                'longitude' => -1.55194,
                'address' => 'QEII Country Park, Ashington, Northumberland NE63 9AT',
                'directions' => 'Enter QEII Country Park near the Lakeside Hotel. Day tickets from Lakeside Hotel, McDermotts Tackle (Ashington) or selected Tyneside tackle shops.',
                'day_ticket_info' => 'Day tickets from Lakeside Hotel, McDermotts Tackle Ashington, Billy\'s Fishing Tackle North Shields, or Frasers Angling Gateshead.',
                'membership_info' => 'Wansbeck and Cramlington AC membership covers QEII plus Brenkley, Horton Grange and Milkhope.',
                'ticket_type' => 'mixed',
                'opening_times' => 'Daylight hours; check club notices.',
                'season_info' => 'Coarse fishing year-round on the stillwater.',
                'tactics_guide' => "Summer silvers respond to waggler and maggot away from weed.\nBream often come from the pit end on feeder at distance with corn or double caster.\nPike from reeds near the museum-end jetty on dead bait or lures.",
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'QEII Lake',
                        'description' => 'Large country-park lake with mixed coarse and pike.',
                        'peg_count' => null,
                        'depth_info' => 'Varied; some deep drop-offs',
                        'species' => ['carp', 'bream', 'roach', 'rudd', 'tench', 'perch', 'pike'],
                    ],
                ],
            ],
            [
                'slug' => 'angel-lakes',
                'name' => 'Angel Lakes',
                'overview' => "Angel of the North Fishing Lakes at Birtley — three mixed coarse lakes over 8 acres with excellent facilities, disabled access and on-site tackle shop.\n\nGold Standard Fisheries Accreditation venue; day tickets and season tickets available.",
                'latitude' => 54.8640,
                'longitude' => -1.5650,
                'address' => 'Angel Fishing, Bassetts Lookout, Northside, Birtley, Chester-le-Street DH3 1RF',
                'directions' => 'From A1 take A1231 Sunderland slip road, left at roundabout toward Wrekenton (Rockcliff Way), then first left into the fishery entrance.',
                'day_ticket_info' => 'Half-day and day tickets available; book online when possible.',
                'membership_info' => 'Season tickets include night fishing rights.',
                'ticket_type' => 'mixed',
                'opening_times' => 'Shop 07:00–12:00 for day tickets; fishing typically 07:00–20:00.',
                'season_info' => 'Open year-round.',
                'tactics_guide' => "Bowes Lake: method feeder and pellet for carp.\nLookout Lake: silvers on maggot and caster; island fishing via bridge.\nBassett's Pond: sheltered water ideal for beginners and families.",
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Bowes Lake',
                        'description' => 'Pleasure and match lake with two islands and 40 pegs.',
                        'peg_count' => 40,
                        'depth_info' => 'Around 7ft',
                        'species' => ['carp', 'roach', 'rudd', 'ide', 'orfe', 'perch', 'bream', 'tench', 'crucian'],
                    ],
                    [
                        'name' => 'Lookout Lake',
                        'description' => 'Three-acre mixed coarse lake with island access.',
                        'peg_count' => 44,
                        'depth_info' => '5–7ft',
                        'species' => ['carp', 'roach', 'rudd', 'orfe', 'ide', 'perch', 'bream', 'tench', 'crucian'],
                    ],
                    [
                        'name' => "Bassett's Pond",
                        'description' => 'Small sheltered pond ideal for novices and short sessions.',
                        'peg_count' => null,
                        'depth_info' => 'Around 6ft',
                        'species' => ['carp', 'roach', 'rudd', 'perch', 'crucian'],
                    ],
                ],
            ],
            [
                'slug' => 'brenkley-pond',
                'name' => 'Brenkley Pond',
                'overview' => 'Attractive 2-acre WACAC pond near Cramlington with coloured water, margin features and ten disabled pegs. Unpredictable mixed bags of carp, silvers and tench.',
                'latitude' => 55.07129,
                'longitude' => -1.64858,
                'address' => 'Brenkley Pond, Seaton Burn area, Northumberland NE13 6',
                'directions' => 'On the Blagdon Estate near Cramlington. WACAC membership required — see wacac.me.uk.',
                'day_ticket_info' => null,
                'membership_info' => 'Wansbeck and Cramlington Angling Club membership. Apply via wacac.me.uk.',
                'ticket_type' => 'club',
                'opening_times' => 'Club water — follow WACAC rules and parking permit requirements.',
                'season_info' => 'Year-round coarse fishing for members.',
                'tactics_guide' => "Margin fishing close in during summer pays on many pegs.\nPole, waggler, swim feeder and surface baits all work.\nGroundbait trial permitted — check current club rules.",
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Brenkley Pond',
                        'description' => '28-peg pond with shallow and deep areas and bankside cover.',
                        'peg_count' => 28,
                        'depth_info' => 'Shallow and deep areas',
                        'species' => ['carp', 'roach', 'rudd', 'orfe', 'perch', 'bream', 'gudgeon', 'tench', 'crucian'],
                    ],
                ],
            ],
            [
                'slug' => 'horton-grange-lake',
                'name' => 'Horton Grange Lake',
                'overview' => 'Former opencast site transformed into a tree-lined WACAC jewel on the Blagdon Estate. Roach, rudd, tench, perch and bream with abundant wildlife.',
                'latitude' => 55.07741,
                'longitude' => -1.67857,
                'address' => 'Horton Grange Lake, Blagdon Estate, Cramlington, Northumberland',
                'directions' => 'WACAC water on the Blagdon Estate. Membership via wacac.me.uk.',
                'day_ticket_info' => null,
                'membership_info' => 'Wansbeck and Cramlington Angling Club.',
                'ticket_type' => 'club',
                'opening_times' => 'Members only.',
                'season_info' => 'Year-round.',
                'tactics_guide' => 'Light tackle for roach and rudd; feeder for bream and tench. Take time to enjoy the wildlife.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Horton Grange Lake',
                        'description' => 'Scenic estate lake around 1.2 hectares.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['roach', 'rudd', 'tench', 'perch', 'bream', 'ide'],
                    ],
                ],
            ],
            [
                'slug' => 'milkhope-lake',
                'name' => 'Milkhope Lake',
                'overview' => 'Historic estate lake on the Blagdon Estate — a sheltered woodland gem with golden orfe, ide, carp, tench and crucians circling three central islands.',
                'latitude' => 55.08015,
                'longitude' => -1.67571,
                'address' => 'Milkhope Lake, Blagdon Estate, Cramlington, Northumberland',
                'directions' => 'Woodland path from the estate car park. WACAC members only.',
                'day_ticket_info' => null,
                'membership_info' => 'Wansbeck and Cramlington Angling Club.',
                'ticket_type' => 'club',
                'opening_times' => 'Members only.',
                'season_info' => 'Year-round.',
                'tactics_guide' => 'Float fishing with bread, caster and maggot for orfe and ide on the surface; tench and crucians on the deck.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Milkhope Lake',
                        'description' => 'Tree-lined estate lake with three islands.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['carp', 'tench', 'crucian', 'ide', 'orfe', 'roach', 'rudd', 'perch'],
                    ],
                ],
            ],
            [
                'slug' => 'throckley-reigh',
                'name' => 'Throckley Reigh',
                'overview' => 'Small TAA pond in Tyne Riverside Country Park, a short walk from Blayney Row. Eight pegs holding perch, roach, rudd, tench, bream and carp.',
                'latitude' => 54.98674,
                'longitude' => -1.76449,
                'address' => 'Throckley Reigh, Tyne Riverside Country Park, near Throckley, Newcastle upon Tyne',
                'directions' => 'Park at the end of Blayney Row, walk behind the houses through the kissing gate and follow the field path for around 10 minutes to the pond.',
                'day_ticket_info' => null,
                'membership_info' => 'Tyne Anglers Alliance members and affiliated clubs only.',
                'ticket_type' => 'club',
                'opening_times' => 'TAA member water.',
                'season_info' => 'No close season on this stillwater.',
                'tactics_guide' => 'Fish light for roach and rudd; pellet and corn for tench and bream. Access is on foot only — travel light.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Throckley Reigh',
                        'description' => 'Eight-peg country-park pond.',
                        'peg_count' => 8,
                        'depth_info' => 'Varied',
                        'species' => ['roach', 'rudd', 'tench', 'bream', 'perch', 'carp', 'crucian', 'pike'],
                    ],
                ],
            ],
            [
                'slug' => 'river-tyne-newburn',
                'name' => 'River Tyne Newburn',
                'overview' => 'TAA stretch of the River Tyne from Tyne Riverside Country Park downstream towards Newcastle. Chub, roach, dace, eels and flounders with salmon and sea trout present seasonally.',
                'latitude' => 54.98330,
                'longitude' => -1.75004,
                'address' => 'River Tyne, Tyne Riverside Country Park, Newburn, Newcastle upon Tyne',
                'directions' => 'Access from Tyne Riverside Country Park at Newburn. TAA membership or affiliated club membership required.',
                'day_ticket_info' => null,
                'membership_info' => 'Tyne Anglers Alliance.',
                'ticket_type' => 'club',
                'opening_times' => 'Follow EA byelaws and TAA river rules.',
                'season_info' => 'Coarse close season 15 March–15 June on rivers. Salmon fishing not before 16 June.',
                'tactics_guide' => 'Feeder and float for chub and roach; worm for eels. Spinning and fly for game fish in season.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Newburn to Newcastle Stretch',
                        'description' => 'River Tyne coarse and game stretch.',
                        'peg_count' => null,
                        'depth_info' => 'River depths vary with flow',
                        'species' => ['chub', 'roach', 'dace', 'eel', 'perch', 'pike', 'brown-trout', 'sea-trout', 'salmon'],
                    ],
                ],
            ],
            [
                'slug' => 'wydon-burn',
                'name' => 'Wydon Burn',
                'overview' => 'Wydon Water flood-storage reservoir near Hexham with 20 pegs. Managed by Hexham Angling Association with limited TAA complimentary tickets daily.',
                'latitude' => 54.96365,
                'longitude' => -2.10838,
                'address' => 'Wydon Water, Wydon Grange, Hexham, Northumberland NE46 2DA',
                'directions' => 'Near Hexham town. Book in advance through Hexham Angling Association. TAA members must pre-book — only six complimentary tickets per day.',
                'day_ticket_info' => 'Day tickets from GOJO and Top Tackle in Hexham for non-members.',
                'membership_info' => 'Hexham AA membership or TAA complimentary ticket (book ahead).',
                'ticket_type' => 'mixed',
                'opening_times' => 'Book visits in advance.',
                'season_info' => 'Stillwater — year-round coarse fishing.',
                'tactics_guide' => 'Float fishing for roach, rudd and tench. Bream reported to upper single figures.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Wydon Water',
                        'description' => '20-peg reservoir up to 8ft deep.',
                        'peg_count' => 20,
                        'depth_info' => 'Up to 8ft',
                        'species' => ['roach', 'rudd', 'tench', 'crucian', 'bream', 'perch'],
                    ],
                ],
            ],
            [
                'slug' => 'fontburn-reservoir',
                'name' => 'Fontburn Reservoir',
                'overview' => "Family-friendly Northumbrian Water reservoir near Rothbury with trout and coarse fishing. Good roach stocks and accessible north-shore platform.\n\nUser-listed as Fontdene — this is Fontburn Reservoir.",
                'latitude' => 55.2970,
                'longitude' => -1.9680,
                'address' => 'Fontburn Reservoir, near Rothbury, Northumberland',
                'directions' => 'Off A696 on B6342 near Rothbury. Permits from the Fontburn shop or bookwhen.com/watersideparksnorth.',
                'day_ticket_info' => 'Coarse day permit approx. £12 (2026). Trout permits also available.',
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Check Waterside Parks North for seasonal hours.',
                'season_info' => 'Check site notices for any temporary restrictions.',
                'tactics_guide' => 'Coarse fishing for roach; trout on fly and spinning elsewhere on the reservoir.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Fontburn Reservoir',
                        'description' => 'Smaller friendly reservoir with coarse and trout zones.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['roach', 'perch', 'pike', 'brown-trout', 'rainbow-trout'],
                    ],
                ],
            ],
            [
                'slug' => 'whittle-dene-reservoir',
                'name' => 'Whittle Dene Reservoir',
                'overview' => 'Three well-maintained coarse reservoirs on the Military Road (B6318) near Harlow Hill. Excellent roach, perch and skimmer fishing in rolling Northumberland countryside.',
                'latitude' => 54.9880,
                'longitude' => -1.8620,
                'address' => 'Whittle Dene Reservoirs, Military Road, Harlow Hill, Northumberland NE15',
                'directions' => 'On the B6318 Military Road west of Newcastle. Permits via bookwhen.com/watersideparksnorth or the Derwent shop.',
                'day_ticket_info' => 'Coarse day permit approx. £9 (2026). Parking included. Up to two under-17s fish free per paying adult.',
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Typically until 21:00 or sunset in summer.',
                'season_info' => 'Year-round coarse fishing.',
                'tactics_guide' => 'Light float tactics for roach; feeder for skimmers and bream on the lower and great southern lakes.',
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Lower Lake',
                        'description' => 'One of three coarse reservoirs at Whittle Dene.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['roach', 'perch', 'bream', 'rudd'],
                    ],
                    [
                        'name' => 'Great Southern Lake',
                        'description' => 'Popular roach and skimmer water.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['roach', 'perch', 'bream', 'rudd', 'tench'],
                    ],
                    [
                        'name' => 'Middle Lake',
                        'description' => 'Middle reservoir of the Whittle Dene group.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['roach', 'perch', 'bream', 'rudd'],
                    ],
                ],
            ],
            [
                'slug' => 'big-waters',
                'name' => 'Big Waters',
                'overview' => '22-acre Big Waters and smaller Little Big Waters at Seaton Burn, run by Big Waters Angling Club. Carp to 26lb, bream to 7lb and reliable winter roach and bream sport.',
                'latitude' => 55.0890,
                'longitude' => -1.5960,
                'address' => 'Big Waters Nature Reserve, Seaton Burn, Newcastle upon Tyne NE13 7BD',
                'directions' => 'Near Seaton Burn north of Newcastle. Club membership required — see bigwatersac.co.uk.',
                'day_ticket_info' => null,
                'membership_info' => 'Big Waters Angling Club. Affiliated to TAA for access to alliance waters.',
                'ticket_type' => 'club',
                'opening_times' => 'Members only.',
                'season_info' => 'Year-round.',
                'tactics_guide' => 'Method feeder and pellet for carp and bream; pleasure nets over 20lb not uncommon in winter.',
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Big Waters',
                        'description' => 'Main 22-acre club lake.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['carp', 'bream', 'roach', 'rudd', 'tench', 'perch', 'pike'],
                    ],
                    [
                        'name' => 'Little Big Waters',
                        'description' => 'Smaller companion lake on the same reserve.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['carp', 'bream', 'roach', 'rudd', 'tench', 'perch'],
                    ],
                ],
            ],
            [
                'slug' => 'stargate',
                'name' => 'Stargate',
                'overview' => 'Ryton and District AC pond at Blaydon Burn — 2 acres, 6–10ft deep with carp to 25lb+, tench to 9lb+ and pike to 19lb. Disabled peg near the car park.',
                'latitude' => 54.9610,
                'longitude' => -1.7220,
                'address' => 'Stargate Pond, Stargate Lane, Blaydon Burn, Tyne and Wear NE21 4',
                'directions' => 'Off Stargate Lane, Blaydon Burn. Ryton and District Angling Club membership required.',
                'day_ticket_info' => null,
                'membership_info' => 'Ryton and District Angling Club. Night fishing after one year membership.',
                'ticket_type' => 'club',
                'opening_times' => 'Club water — see rytonanddistrictanglingclub.co.uk.',
                'season_info' => 'Stillwater coarse year-round; keepnets restricted in close season except club matches.',
                'tactics_guide' => 'All methods work. Margins and island features hold carp and tench. Predator fishing for pike and eels.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Stargate Pond',
                        'description' => 'Established 2-acre club pond with gravel bars.',
                        'peg_count' => null,
                        'depth_info' => '6–10ft',
                        'species' => ['carp', 'tench', 'bream', 'chub', 'roach', 'rudd', 'perch', 'pike', 'eel'],
                    ],
                ],
            ],
            [
                'slug' => 'meggies-burn-reservoir',
                'name' => 'Meggies Burn Reservoir',
                'overview' => 'Blyth Freshwater Angling Club water in Blyth — mixed coarse pond with improved access paths and pegs.',
                'latitude' => 55.100191,
                'longitude' => -1.513551,
                'address' => 'Meggies Burn, Blyth, Northumberland',
                'directions' => 'Blyth FAC club water. See blythfac.co.uk for access and membership.',
                'day_ticket_info' => null,
                'membership_info' => 'Blyth Freshwater Angling Club.',
                'ticket_type' => 'club',
                'opening_times' => 'Club members only.',
                'season_info' => 'Year-round on stillwater.',
                'tactics_guide' => 'Mixed coarse tactics on pole and waggler.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Meggies Burn Pond',
                        'description' => 'Club coarse pond with upgraded pegs and paths.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['carp', 'roach', 'rudd', 'tench', 'perch', 'bream'],
                    ],
                ],
            ],
            [
                'slug' => 'bolam-lake',
                'name' => 'Bolam Lake',
                'overview' => 'Bolam Lake Country Park coarse fishery west of Morpeth. Bream, perch, pike, roach and tench in a scenic woodland setting with café and visitor centre.',
                'latitude' => 55.132840,
                'longitude' => -1.871622,
                'address' => 'Bolam Lake Country Park, near Belsay, Northumberland NE20 0HE',
                'directions' => 'Nine miles west of Morpeth via A696 from Belsay. Day and season tickets from Belford Post Office.',
                'day_ticket_info' => 'Northumberland County Council permits from Belford Post Office.',
                'membership_info' => 'Season tickets also available.',
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Bank fishing in daylight hours only.',
                'season_info' => 'Closed season 16 June to 14 March.',
                'tactics_guide' => 'Ledger and float for bream and roach; pike in cooler months.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Bolam Lake',
                        'description' => 'Country park lake with circular lakeside walk.',
                        'peg_count' => null,
                        'depth_info' => 'Varied',
                        'species' => ['bream', 'perch', 'pike', 'roach', 'tench'],
                    ],
                ],
            ],
            [
                'slug' => 'derwent-reservoir',
                'name' => 'Derwent Reservoir Fisheries',
                'overview' => "Northumbrian Water's most popular reservoir near Edmundbyers — extensive bank fishing for quality roach and pike, plus trout on fly and any-method permits.",
                'latitude' => 54.8605,
                'longitude' => -1.9784,
                'address' => 'Derwent Reservoir, Edmundbyers, County Durham DH8 9TT',
                'directions' => 'Visitor centre car parks on the reservoir. Permits from the café/shop or bookwhen.com/watersideparksnorth.',
                'day_ticket_info' => 'Coarse/pike day permit approx. £12 (2026). Parking included in permit.',
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Shop typically 08:00–17:00; check Waterside Parks.',
                'season_info' => 'Check site notices for any restrictions.',
                'tactics_guide' => 'Any-method coarse fishing for roach; pike on dead baits and lures. Disabled platform on south side of dam.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Derwent Reservoir',
                        'description' => 'Large upland reservoir with coarse and game fishing.',
                        'peg_count' => null,
                        'depth_info' => 'Deep drop-offs near dam',
                        'species' => ['roach', 'perch', 'pike', 'brown-trout', 'rainbow-trout'],
                    ],
                ],
            ],
            [
                'slug' => 'tunstall-reservoir',
                'name' => 'Tunstall Reservoir',
                'overview' => '66-acre upland reservoir on Waskerley Beck near Wolsingham. Fly-only trout fishery for Tunstall Fly Fishers club members — wild brown trout and stocked rainbows.',
                'latitude' => 54.7280,
                'longitude' => -1.8830,
                'address' => 'Tunstall Reservoir, Wolsingham, Bishop Auckland DL13 3LX',
                'directions' => 'Wooded valley near Wolsingham. Members-only via Tunstall Fly Fishers — waiting list applies.',
                'day_ticket_info' => null,
                'membership_info' => 'Tunstall Fly Fishers club only (approx. 200 members, seasonal fee).',
                'ticket_type' => 'syndicate',
                'opening_times' => 'Season typically 22 March to 30 September.',
                'season_info' => 'Trout season 22 March–30 September. Catch-and-release after first fish.',
                'tactics_guide' => 'Traditional wet flies, lures, tadpoles and small black terrestrials.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Tunstall Reservoir',
                        'description' => 'Fly-only trout reservoir in wooded valley.',
                        'peg_count' => null,
                        'depth_info' => 'Deep coloured water',
                        'species' => ['brown-trout', 'rainbow-trout'],
                    ],
                ],
            ],
            [
                'slug' => 'aldin-grange',
                'name' => 'Aldin Grange Fishery',
                'overview' => "Popular Durham complex with match and specimen waters near Bearpark. On-site café at weekends, steady carp and silverfish stocks.\n\nAlready a featured venue — migration refreshes listing data.",
                'latitude' => 54.7912,
                'longitude' => -1.6208,
                'address' => 'Aldin Grange Farm, Bearpark, Durham DH7 7AR',
                'directions' => 'From Durham follow A691 towards Consett, then local signs for Bearpark / Aldin Grange.',
                'day_ticket_info' => 'Adult day tickets from the bailiff on the bank. Night fishing by prior arrangement.',
                'membership_info' => 'Annual club tickets from the on-site cabin.',
                'ticket_type' => 'mixed',
                'opening_times' => 'Dawn until dusk for day tickets.',
                'season_info' => 'Open year-round on stillwaters.',
                'tactics_guide' => 'Method feeder on Match Lake; zig rigs and solid bags after dark on Specimen Lake.',
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Match Lake',
                        'description' => 'Island-feature match water with even pegging.',
                        'peg_count' => 28,
                        'depth_info' => '4–7ft',
                        'species' => ['carp', 'f1', 'bream', 'roach', 'perch'],
                    ],
                    [
                        'name' => 'Specimen Lake',
                        'description' => 'Larger carp water with reed-lined margins and two islands.',
                        'peg_count' => 16,
                        'depth_info' => '6–12ft',
                        'species' => ['carp', 'tench', 'pike'],
                    ],
                ],
            ],
            [
                'slug' => 'batleys-pond',
                'name' => "Batley's Pond",
                'overview' => "Small local pool between Birtley and Chester-le-Street, historically known as Batley's Pond. Managed by Batleys Fishing Club CIC — check current membership status before visiting.",
                'latitude' => 54.87105,
                'longitude' => -1.58236,
                'address' => "Batley's Pond, between Birtley and Chester-le-Street, County Durham",
                'directions' => 'Near Drum Industrial Estate between Birtley and Chester-le-Street. Grid reference NZ 269 530.',
                'day_ticket_info' => 'Club membership — verify availability with Batleys Fishing Club CIC.',
                'membership_info' => 'Batleys Fishing Club CIC (Birtley area).',
                'ticket_type' => 'club',
                'opening_times' => 'Club water — confirm access before travelling.',
                'season_info' => 'Check club rules.',
                'tactics_guide' => 'Small-pool tactics with pole and waggler.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => "Batley's Pond",
                        'description' => 'Small local coarse pond.',
                        'peg_count' => null,
                        'depth_info' => 'Shallow',
                        'species' => ['carp', 'roach', 'rudd', 'tench', 'perch'],
                    ],
                ],
            ],
        ];
    }
};
