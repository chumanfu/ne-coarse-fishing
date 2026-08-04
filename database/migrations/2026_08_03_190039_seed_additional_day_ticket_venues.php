<?php

use App\Models\Club;
use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** @var array<string, int> */
    private array $speciesSlugs = [];

    public function up(): void
    {
        $admin = User::query()
            ->where('email', 'admin@nefishing.test')
            ->first() ?? User::query()->first();

        if (! $admin) {
            return;
        }

        $this->speciesSlugs = Species::query()->pluck('id', 'slug')->all();

        foreach ($this->clubs() as $club) {
            Club::query()->updateOrCreate(
                ['slug' => $club['slug']],
                $club,
            );
        }

        foreach ($this->venues() as $venueData) {
            $waters = $venueData['waters'] ?? [];
            $clubSlugs = $venueData['clubs'] ?? [];
            unset($venueData['waters'], $venueData['clubs']);

            $venue = Venue::query()->updateOrCreate(
                ['slug' => $venueData['slug']],
                [
                    ...$venueData,
                    'user_id' => $admin->id,
                    'is_approved' => true,
                    'manager_verified' => false,
                ],
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
                    ],
                );

                if ($species !== []) {
                    $water->species()->sync($this->speciesIds($species));
                }
            }

            $clubIds = Club::query()->whereIn('slug', $clubSlugs)->pluck('id');
            if ($clubIds->isNotEmpty()) {
                $venue->clubs()->syncWithoutDetaching($clubIds);
            }
        }
    }

    public function down(): void
    {
        $venueSlugs = array_column($this->venues(), 'slug');

        Venue::query()
            ->whereIn('slug', $venueSlugs)
            ->each(function (Venue $venue): void {
                $venue->clubs()->detach();
                $venue->waters()->each(function (Water $water): void {
                    $water->species()->detach();
                    $water->delete();
                });
                $venue->delete();
            });

        Club::query()
            ->whereIn('slug', array_column($this->clubs(), 'slug'))
            ->each(function (Club $club): void {
                $club->venues()->detach();
                $club->delete();
            });
    }

    /**
     * @param  list<string>  $slugs
     * @return list<int>
     */
    private function speciesIds(array $slugs): array
    {
        $ids = [];

        foreach ($slugs as $slug) {
            if (isset($this->speciesSlugs[$slug])) {
                $ids[] = $this->speciesSlugs[$slug];
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function clubs(): array
    {
        return [
            [
                'name' => 'Billingham Angling Club',
                'slug' => 'billingham-angling-club',
                'url' => 'https://www.billinghamanglingclub.co.uk/',
                'overview' => 'Teesside club managing Charlton’s Pond in Billingham — a mixed nature-reserve fishery with a day-ticket small pond and members-only large pond.',
                'town' => 'Billingham',
                'address' => "Charlton's Pond, Rear of Hereford Terrace, Stockton-on-Tees, TS23 4AA",
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 40,
                'is_published' => true,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function venues(): array
    {
        $mixedCommercial = ['carp', 'common-carp', 'mirror-carp', 'f1', 'tench', 'bream', 'roach', 'rudd', 'perch', 'ide', 'barbel', 'chub', 'orfe', 'gudgeon', 'crucian'];

        return [
            [
                'name' => 'The Oaks Lakes',
                'slug' => 'the-oaks-lakes-sessay',
                'overview' => "Large commercial match and pleasure complex at Sessay, near Thirsk — 10 lakes and 400+ pegs with café, tackle shop and a small caravan/camping park.\n\nOpened in 1994 with Willows and steadily expanded. Lakes include Ash, Beech, Sycamore, Alders, Cedar, Maple, Poplars, Firs, Oaks and specimen Willows.",
                'latitude' => 54.1788033,
                'longitude' => -1.3096791,
                'address' => 'The Oaks Lakes, Sessay, Thirsk, North Yorkshire, YO7 3BG',
                'url' => 'https://www.theoakslakes.co.uk/',
                'directions' => 'Sessay sits about a mile inland from the A19 between York and Thirsk. Sat nav YO7 3BJ. The complex is at the north end of Sessay village / between Dalton and Sessay, handy from A1(M) J49 via the A168/A19.',
                'day_ticket_info' => "Day ticket £12; Willows £14; evening from 4pm £6; seniors £10; juniors under 14 (with adult) £10.\nFishing enquiries: 01845 501321. Willows by appointment: 07730 531899.\nBarbless hooks only; fishery feed pellets only; max two rods. Confirm current prices on theoakslakes.co.uk.",
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Summer typically 6:30am–9:00pm; winter dawn–dusk. Confirm on site / website.',
                'season_info' => 'Open year-round subject to matches and fishery notices.',
                'tactics_guide' => 'Commercial-style fishing across snake/island match lakes and mixed pleasure waters. Pellet, meat, corn and maggot dominate; pole across to islands on Maple/Cedar/Poplars. Willows is the specimen carp water (heavier gear by appointment).',
                'is_complex' => true,
                'waters' => [
                    ['name' => 'Alders', 'description' => '41-peg match/pleasure lake with island fishing; carp to doubles plus chub, silvers, tench, ide, barbel and orfe.', 'peg_count' => 41, 'species' => $mixedCommercial],
                    ['name' => 'Ash', 'description' => '24-peg match lake — open water design with carp, F1s, ide and barbel.', 'peg_count' => 24, 'species' => ['carp', 'f1', 'ide', 'barbel']],
                    ['name' => 'Beech', 'description' => '26-peg match lake alongside Ash and Sycamore.', 'peg_count' => 26, 'species' => ['carp', 'f1', 'ide', 'barbel']],
                    ['name' => 'Sycamore', 'description' => '30-peg match lake — commons, mirrors, F1s, ide and barbel.', 'peg_count' => 30, 'species' => ['carp', 'f1', 'ide', 'barbel']],
                    ['name' => 'Cedar', 'description' => '80-peg purpose-built snake/island match lake; F1s, carp, ide, bream and barbel. Regular open matches.', 'peg_count' => 80, 'depth_info' => 'About 5–6ft down the middle; island shelf ~12–18in', 'species' => ['carp', 'f1', 'ghost-carp', 'ide', 'bream', 'barbel']],
                    ['name' => 'Maple', 'description' => '68-peg island match lake; carp mainly 8oz–5lb with bonuses to ~10lb, plus silvers, tench and barbel.', 'peg_count' => 68, 'depth_info' => 'Average ~5ft; shelf at 11–12m', 'species' => ['carp', 'roach', 'rudd', 'tench', 'barbel', 'ide', 'gudgeon']],
                    ['name' => 'Poplars', 'description' => 'Match/pleasure lake designed so pole anglers can reach the central island; carp to ~9lb plus mixed species.', 'peg_count' => 55, 'species' => ['carp', 'tench', 'ide', 'barbel', 'roach', 'perch', 'rudd', 'bream']],
                    ['name' => 'Firs', 'description' => 'Pleasure lake with carp to mid-doubles and a mixed silver/tench stock.', 'peg_count' => 25, 'species' => ['carp', 'tench', 'bream', 'roach', 'perch', 'barbel', 'ide', 'gudgeon']],
                    ['name' => 'Oaks', 'description' => '35-peg mixed pleasure lake; carp to doubles plus bream, tench, crucians, ide and orfe.', 'peg_count' => 35, 'species' => $mixedCommercial],
                    ['name' => 'Willows', 'description' => 'Specimen lake by appointment — commons/mirrors to upper twenties, ghost and grass carp. Pleasure angling only.', 'peg_count' => 30, 'depth_info' => 'Around 7ft with central island', 'species' => ['carp', 'common-carp', 'mirror-carp', 'ghost-carp', 'grass-carp']],
                ],
            ],
            [
                'name' => "Charlton's Pond",
                'slug' => 'charltons-pond',
                'overview' => "Two-pond nature-reserve fishery in Billingham managed by Billingham Angling Club. Former clay brick workings flooded by a spring; the large pond was stocked with Loch Leven trout in the early 20th century and is now a mixed coarse water.\n\nSmall pond (~1 acre) offers day tickets; large pond (~6–8 acres) is members only. Disabled-access pegs on both waters.",
                'latitude' => 54.6017231,
                'longitude' => -1.2760788,
                'address' => "Charlton's Pond, Rear of Hereford Terrace / Lincoln Crescent, Billingham, TS23 4AA",
                'url' => 'https://www.billinghamanglingclub.co.uk/',
                'directions' => 'In Billingham Green / Cowpen Bewley area. Access via Hereford Terrace / Lincoln Crescent (TS23 4AA / TS23 4BL). Parking by the ponds with easy paths around the small pond.',
                'day_ticket_info' => 'Day tickets available on the Small Pond — check Billingham Angling Club for current prices and how to buy. Large Pond is members only.',
                'membership_info' => 'Join Billingham Angling Club for Large Pond access and full club waters. Email BillinghamAC.info@gmail.com — details on billinghamanglingclub.co.uk.',
                'ticket_type' => 'mixed',
                'opening_times' => 'Generally open; confirm with Billingham Angling Club.',
                'season_info' => 'Mixed coarse fishing year-round subject to club rules and conservation areas.',
                'tactics_guide' => 'Small Pond suits all abilities — carp, crucians, silvers, ide, tench and bream on pole, waggler and feeder. Large Pond holds carp into the twenties/thirties plus quality bream and tench; longer casts and margin work both score.',
                'is_complex' => true,
                'clubs' => ['billingham-angling-club'],
                'waters' => [
                    [
                        'name' => 'Small Pond',
                        'description' => 'About 1 acre with ~22 pegs and paths all round; pegs 21–22 wheelchair-friendly. Day-ticket mixed fishery; junior matches in summer.',
                        'peg_count' => 22,
                        'species' => ['carp', 'crucian', 'roach', 'rudd', 'ide', 'perch', 'tench', 'bream', 'gudgeon'],
                    ],
                    [
                        'name' => 'Large Pond',
                        'description' => 'Members-only ~6–8 acre lake — carp to upper twenties/30lb, bream to ~9lb, tench to ~7lb, plus perch, roach, rudd and crucians. Bird conservation area on part of the water.',
                        'species' => ['carp', 'common-carp', 'mirror-carp', 'bream', 'tench', 'perch', 'roach', 'rudd', 'crucian'],
                    ],
                ],
            ],
            [
                'name' => 'Green Lane Ponds',
                'slug' => 'green-lane-ponds',
                'overview' => "Day- and night-ticket mixed coarse fishery at Green Lane Farm, Scorton (near Catterick), North Yorkshire — also known as Green Lane Fisheries.\n\nTwo lakes (Main Lake and Snake Lake) stocked with carp, tench, bream, roach, rudd, perch, ide and barbel. Toilets on site; net dip at the gate.",
                'latitude' => 54.4179,
                'longitude' => -1.60011,
                'address' => 'Green Lane Farm, Scorton, Richmond, North Yorkshire, DL10 6EN',
                'url' => 'https://greenlanefarmfisheries.co.uk/',
                'directions' => 'Green Lane Farm on the Scorton / Catterick side of North Yorkshire (DL10 6EN). Contact the fishery if unsure of the farm entrance.',
                'day_ticket_info' => "Day: adults 2 rods £10 / 3 rods £15; concessions 1 rod £8 / 2 rods £10.\nNight (24hrs): adults 2 rods £20 / 3 rods £25. Guest camping £5.\nPhone: 07779 817258. Confirm on greenlanefarmfisheries.co.uk/prices.html.",
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Gates typically 7am until 8pm (or dusk). Day and night fishing welcome — check current hours.',
                'season_info' => 'Open year-round subject to fishery notices.',
                'tactics_guide' => 'Dip nets and mats at the gate before fishing. Mixed commercial stocks on Main and Snake lakes — pellet, corn, meat and maggot all work.',
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Main Lake',
                        'description' => 'Main mixed coarse lake with carp, tench, bream and silvers.',
                        'species' => ['carp', 'tench', 'bream', 'roach', 'rudd', 'perch', 'ide', 'barbel'],
                    ],
                    [
                        'name' => 'Snake Lake',
                        'description' => 'Snake-style second lake on the Green Lane complex.',
                        'species' => ['carp', 'tench', 'bream', 'roach', 'rudd', 'perch', 'ide', 'barbel'],
                    ],
                ],
            ],
            [
                'name' => 'Renny Lakes',
                'slug' => 'renny-lakes',
                'overview' => "Family-run day-ticket coarse fishery at East Harlsey, North Yorkshire, sheltered by the Cleveland Hills.\n\nTypically two fishable lakes (historically three) with carp to around 15lb plus chub, tench, roach and rudd. Toilets and parking on site; Lake 2 available for matches by arrangement.",
                'latitude' => 54.4005928,
                'longitude' => -1.3550858,
                'address' => 'Renny Lakes, Deepdale, East Harlsey, Northallerton, North Yorkshire, DL6 2EA',
                'url' => 'https://www.rennylake.co.uk/',
                'directions' => 'East Harlsey village area between Northallerton and the Cleveland Hills. Use DL6 2EA / Deepdale and follow local signs for Renny Lakes.',
                'day_ticket_info' => "Adults £15; OAP / children under 15 £10. Evening ticket (4pm–8pm) £10.\nBarbless hooks only; no keepnets; landing net required; no night fishing; no dogs. Phone often listed as 07714 253662 — confirm on rennylake.co.uk.",
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Daily 7:00am–8:00pm.',
                'season_info' => 'Open all year; check homepage announcements for lake availability.',
                'tactics_guide' => 'Island and open-water pegs suit waggler, feeder and pole. Maggot, corn and pellet produce carp and silvers; keep feeding tight when small fish are thick.',
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Lake 1',
                        'description' => 'Main pleasure lake with islands and open water; carp, chub, tench and silvers.',
                        'species' => ['carp', 'chub', 'tench', 'roach', 'rudd'],
                    ],
                    [
                        'name' => 'Lake 2',
                        'description' => 'Second lake — available for matches by arrangement with the fishery.',
                        'species' => ['carp', 'chub', 'tench', 'roach', 'rudd'],
                    ],
                ],
            ],
            [
                'name' => 'Woodland Lakes',
                'slug' => 'woodland-lakes',
                'overview' => "Award-winning commercial complex at Carlton Miniott near Thirsk — often called Woodlands by Teesside and North Yorkshire anglers.\n\nAround 11–13 landscaped lakes across ~45 acres with 300+ pegs, tackle shop, café and on-site bar/restaurant. Day ticket and night fishing (Kingfisher / Catfish lakes). Dogs on leads welcome.",
                'latitude' => 54.2188574,
                'longitude' => -1.4045487,
                'address' => 'Woodland Lakes, Carlton Miniott, Thirsk, North Yorkshire, YO7 4NJ',
                'url' => 'https://www.woodlandlakesfishery.com/',
                'directions' => 'Minutes from the A19 / A168 at Carlton Miniott, Thirsk (YO7 4NJ). Tarmac roadway and multiple car parks around the complex — a trolley helps for farther lakes.',
                'day_ticket_info' => "Day tickets typically around £7–£10 (concessions £6–£8) depending on season — confirm with the fishery (often 07831 824870).\nNight fishing bookable on specimen lakes. Fishery feed pellets only on match waters; check current rules in the shop.",
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Commercial day-ticket hours; night sessions on designated lakes — book ahead.',
                'season_info' => 'Year-round; several lakes kept open for pleasure when others are on match.',
                'tactics_guide' => 'Partridge and Skylark popular for carp on pellet waggler and tip; Island lakes and square waters for match bags. Pole, bomb and waggler for silvers in winter. No floating baits/bread on many match lakes — check shop rules.',
                'is_complex' => true,
                'waters' => [
                    ['name' => 'Partridge', 'description' => 'Popular carp/match lake.', 'species' => ['carp', 'tench', 'bream', 'roach', 'barbel', 'orfe']],
                    ['name' => 'Skylark', 'description' => 'Match/pleasure carp water.', 'species' => ['carp', 'tench', 'bream', 'roach', 'barbel']],
                    ['name' => 'Kestrel', 'description' => 'Well-stocked lake reached via the tunnel path from the central car parks.', 'species' => ['carp', 'tench', 'bream', 'roach', 'rudd', 'perch']],
                    ['name' => 'Curlew', 'description' => 'Mixed commercial lake on the Woodland complex.', 'species' => ['carp', 'tench', 'bream', 'roach', 'rudd', 'perch', 'barbel', 'orfe']],
                    ['name' => 'Wagtail', 'description' => 'Mixed commercial lake.', 'species' => ['carp', 'tench', 'bream', 'roach', 'rudd', 'perch']],
                    ['name' => 'Nytro', 'description' => 'Mixed stock including quality tench and bream.', 'species' => ['carp', 'tench', 'bream', 'roach', 'rudd', 'perch']],
                    ['name' => 'Silver Birch', 'description' => 'Mixed lake (also known as D-Day / Silverberch) with strong silver and tench sport.', 'species' => ['carp', 'tench', 'bream', 'roach', 'rudd', 'perch']],
                    ['name' => 'Kingfisher', 'description' => 'Premium night-fishing specimen lake — book ahead.', 'species' => ['carp', 'common-carp', 'mirror-carp']],
                    ['name' => 'Catfish Lake', 'description' => 'Night-fishing specimen water targeting bigger fish — book ahead.', 'species' => ['carp', 'wels-catfish']],
                ],
            ],
            [
                'name' => 'Watergate Lake',
                'slug' => 'watergate-lake',
                'overview' => "Specialist day- and 24-hour ticket carp/coarse lake near Murton / Seaham, County Durham — about 3 acres with 10 purpose-built platforms.\n\nCarp into the mid-twenties plus bream, roach, tench, chub, perch and rudd. Book pegs online in advance (no cash on the bank).",
                'latitude' => 54.840346,
                'longitude' => -1.337517,
                'address' => 'Watergate Fishing Lake, Dalton-le-Dale / Murton, Seaham, County Durham, SR7 9EN',
                'url' => 'https://lakebookings.com/lake/seaham-watergate-fishing-lake/',
                'directions' => 'Close to Murton and Dalton Park / Seaham (use fishery directions when booking). Ten numbered platform swims; parking on site.',
                'day_ticket_info' => "Book online only — typically ~£12.50 day ticket and from ~£24 for 24hrs via LakeBookings (optional +£5 third rod / guest).\nDay tickets from 10:00 (or 7:00 if peg free overnight); 24hr changeover 08:30. Contact/WhatsApp often listed as Lee 07599 466843.",
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Seven days; sessions by pre-booked ticket times.',
                'season_info' => 'Year-round subject to online availability.',
                'tactics_guide' => 'Fish from platforms only. Barbless/micro-barb; no fixed rigs; min 8lb line and carp rods; unhooking mat and 22in+ landing net. No nuts/tigers; dip nets before fishing. Margins late in the day for bigger fish.',
                'is_complex' => false,
                'waters' => [
                    [
                        'name' => 'Watergate Lake',
                        'description' => '3-acre spring-fed lake with 10 wood-chip platforms; carp to 25lb+ and mixed coarse.',
                        'peg_count' => 10,
                        'species' => ['carp', 'common-carp', 'mirror-carp', 'bream', 'roach', 'tench', 'chub', 'perch', 'rudd'],
                    ],
                ],
            ],
            [
                'name' => 'Wingate Ponds',
                'slug' => 'wingate-ponds',
                'overview' => "Day-ticket three-lake complex on Wingate Road, County Durham — also known as Eden Meadows Fishery.\n\nMeadow Lake is the main pleasure water (~30 pegs); Cherry Tree and Acer are smaller match-style lakes stocked with carp and tench. On-site parking with wheelchair access to pegs; toilets and a small shop historically available.",
                'latitude' => 54.7205,
                'longitude' => -1.3920,
                'address' => 'Wingate Road, Wingate / Station Town, County Durham, TS28 5LZ',
                'url' => null,
                'directions' => 'On Wingate Road between Station Town and Trimdon Station (TS28 5LZ). Drive-down access for disabled anglers when permitted.',
                'day_ticket_info' => 'Historically around £7 adult, £5 disabled/junior, £4 evening — closed Mondays except bank holidays. Confirm current opening and prices before travelling (venue status can change).',
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Typically Tue–Sun; closed Mondays (except bank holidays) — verify before visiting.',
                'season_info' => 'Most of the year when open.',
                'tactics_guide' => 'Pole across to far banks on Acer/Cherry Tree for carp and tench; Meadow suits pleasure bags on pellet, corn and maggot including occasional double-figure carp.',
                'is_complex' => true,
                'waters' => [
                    [
                        'name' => 'Meadow Lake',
                        'description' => 'Largest lake (~30 pegs) — mixed pleasure fishing with carp including doubles.',
                        'peg_count' => 30,
                        'species' => ['carp', 'tench', 'bream', 'roach', 'rudd', 'perch'],
                    ],
                    [
                        'name' => 'Cherry Tree',
                        'description' => 'Smaller match lake (~12 pegs); carp and tench with silvers when conditions are hard.',
                        'peg_count' => 12,
                        'species' => ['carp', 'tench', 'roach', 'bream'],
                    ],
                    [
                        'name' => 'Acer Lake',
                        'description' => 'Match lake well stocked with carp and tench; pole to the far bank is a banker method.',
                        'species' => ['carp', 'tench'],
                    ],
                ],
            ],
        ];
    }
};
