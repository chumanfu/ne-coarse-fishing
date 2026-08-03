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

        foreach ($this->existingLinks() as $clubSlug => $venueSlugs) {
            $club = Club::query()->where('slug', $clubSlug)->first();
            if (! $club) {
                continue;
            }

            $venueIds = Venue::query()->whereIn('slug', $venueSlugs)->pluck('id');
            if ($venueIds->isNotEmpty()) {
                $club->venues()->syncWithoutDetaching($venueIds);
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
                'name' => 'Northumbrian Anglers Federation',
                'slug' => 'northumbrian-anglers-federation',
                'url' => 'https://northumbrian-angler.co.uk/',
                'overview' => 'One of the UK’s oldest angling federations (est. 1894), offering season and visitor permits on classic Northumberland game waters — beats on the Tyne, Coquet and Till for salmon, sea trout and brown trout.',
                'town' => 'Northumberland',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 15,
                'is_published' => true,
            ],
            [
                'name' => 'Newcastle & District Angling Association',
                'slug' => 'newcastle-district-angling-association',
                'url' => null,
                'overview' => 'Long-standing Newcastle association listed among North East freshwater clubs, representing local anglers across Tyne and Wear.',
                'town' => 'Newcastle upon Tyne',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 145,
                'is_published' => true,
            ],
            [
                'name' => 'Sunderland Freshwater Angling Club',
                'slug' => 'sunderland-freshwater-angling-club',
                'url' => null,
                'overview' => 'Manages the Silksworth Lakes complex on the reclaimed Silksworth Pit site — popular mixed coarse waters for members and day-ticket visitors.',
                'town' => 'Sunderland',
                'address' => 'Silksworth, Sunderland, SR3',
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 55,
                'is_published' => true,
            ],
            [
                'name' => 'Sunderland & District Angling Society',
                'slug' => 'sunderland-district-angling-society',
                'url' => null,
                'overview' => 'Historic Sunderland freshwater society listed among regional North East clubs, serving anglers across the city and Wearside.',
                'town' => 'Sunderland',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 150,
                'is_published' => true,
            ],
            [
                'name' => 'Darlington Anglers Club',
                'slug' => 'darlington-anglers-club',
                'url' => 'https://darlingtonanglersclub.org.uk/',
                'overview' => 'Established 1894. Around nine miles of River Tees fishing from High Coniscliffe to Croft, plus Cleasby Lake stocked with trout and coarse fish. Catch-and-release on all club waters.',
                'town' => 'Darlington',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 65,
                'is_published' => true,
            ],
            [
                'name' => 'Bishop Auckland and District Angling Club',
                'slug' => 'bishop-auckland-district-angling-club',
                'url' => 'https://bishopaucklandanglingclub.com/',
                'overview' => 'BADAC (est. 1936) offers over 17 miles of River Wear game fishing with 45+ named pools for salmon, sea trout, brown trout and grayling between Witton and Croxdale.',
                'town' => 'Bishop Auckland',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 75,
                'is_published' => true,
            ],
            [
                'name' => 'Ferryhill and District Angling Club',
                'slug' => 'ferryhill-district-angling-club',
                'url' => 'https://www.ferryhillanddistrictanglingclub.com/',
                'overview' => 'Formed in 1953. Mixed club with coarse ponds at Mainsforth and The Tilery plus river beats on the Wear, Tyne and Swale for game and coarse anglers.',
                'town' => 'Ferryhill',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 85,
                'is_published' => true,
            ],
            [
                'name' => 'Hetton Lyons Angling Club',
                'slug' => 'hetton-lyons-angling-club',
                'url' => 'https://hlac.co.uk/',
                'overview' => 'Public-park coarse club at Hetton-le-Hole with Stephensons Lake (match / day ticket) and Lyons Lake (members-only carp water with night fishing).',
                'town' => 'Hetton-le-Hole',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 95,
                'is_published' => true,
            ],
            [
                'name' => 'Aycliffe Angling Club',
                'slug' => 'aycliffe-angling-club',
                'url' => 'https://aycliffeanglingclub1973.godaddysites.com/',
                'overview' => 'Newton Aycliffe club (est. 1973) running local coarse pond fishing for members in County Durham.',
                'town' => 'Newton Aycliffe',
                'address' => 'Newton Aycliffe, DL4',
                'phone' => '07825 813833',
                'is_featured' => false,
                'sort_order' => 105,
                'is_published' => true,
            ],
            [
                'name' => 'Four in One Angling Club',
                'slug' => 'four-in-one-angling-club',
                'url' => null,
                'overview' => 'Corbridge-area coarse club associated with Thornbrough Pond, offering junior-friendly stillwater fishing in the Tyne Valley.',
                'town' => 'Corbridge',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 115,
                'is_published' => true,
            ],
            [
                'name' => 'Lakeside Angling Club',
                'slug' => 'lakeside-angling-club',
                'url' => null,
                'overview' => 'Hebburn club running Pelaw Quarry Pond behind the Cock Crow Inn, with access to shared Tyne Anglers Alliance waters.',
                'town' => 'Hebburn',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 125,
                'is_published' => true,
            ],
            [
                'name' => 'Axwell Park and Derwent Valley Angling Association',
                'slug' => 'axwell-park-derwent-valley-angling-association',
                'url' => 'https://www.apdvaa.co.uk/',
                'overview' => 'Holds fishing rights in Derwent Walk Country Park / Derwenthaugh Park for local coarse and game anglers along the Derwent Valley.',
                'town' => 'Gateshead',
                'address' => null,
                'phone' => '01207 543426',
                'is_featured' => false,
                'sort_order' => 135,
                'is_published' => true,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function venues(): array
    {
        return [
            [
                'name' => 'Brasside Lakes',
                'slug' => 'brasside-lakes',
                'overview' => 'Durham City Angling Club’s five-lake stillwater complex at Brasside — canal, ponds and specimen waters for coarse and match anglers. Members only; no day tickets.',
                'latitude' => 54.8025,
                'longitude' => -1.5405,
                'address' => 'Brasside, Durham',
                'url' => 'https://www.durhamanglers.co.uk/',
                'ticket_type' => 'club',
                'membership_info' => 'DCAC membership required. Brasside gate code on annual membership documentation.',
                'day_ticket_info' => 'No day tickets.',
                'is_complex' => true,
                'clubs' => ['durham-city-angling-club'],
                'waters' => [
                    ['name' => 'East Lake', 'description' => 'Mixed coarse lake at Brasside.', 'species' => ['carp', 'bream', 'tench', 'roach', 'crucian']],
                    ['name' => 'West Lake', 'description' => 'Specimen / carp water.', 'species' => ['carp', 'mirror-carp', 'common-carp']],
                    ['name' => 'Canal', 'description' => 'Canal-style match water.', 'species' => ['roach', 'bream', 'perch', 'carp']],
                    ['name' => 'New Pond', 'description' => 'Stillwater pond on the Brasside complex.', 'species' => ['carp', 'tench', 'bream', 'crucian']],
                    ['name' => 'Backwater', 'description' => 'Quieter Brasside pool.', 'species' => ['carp', 'roach', 'bream']],
                ],
            ],
            [
                'name' => 'River Wear (Durham City)',
                'slug' => 'river-wear-durham-city',
                'overview' => 'Durham City Angling Club river beats on the Wear in and around Durham for salmon, sea trout and specialist coarse fishing.',
                'latitude' => 54.7750,
                'longitude' => -1.5760,
                'address' => 'River Wear, Durham',
                'url' => 'https://www.durhamanglers.co.uk/',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['durham-city-angling-club'],
                'waters' => [
                    ['name' => 'Durham City Wear beats', 'description' => 'Club stretches of the River Wear around Durham.', 'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'chub', 'barbel']],
                ],
            ],
            [
                'name' => 'Silksworth Lakes',
                'slug' => 'silksworth-lakes',
                'overview' => 'Reclaimed Silksworth Pit parkland lakes run by Sunderland Freshwater Angling Club. Mixed coarse fishing with match and pleasure pegs; day tickets available — check which waters are open.',
                'latitude' => 54.8730,
                'longitude' => -1.4100,
                'address' => 'Silksworth, Sunderland, SR3',
                'url' => null,
                'ticket_type' => 'mixed',
                'day_ticket_info' => 'Day tickets available subject to club notices — confirm open waters and prices before travelling.',
                'is_complex' => true,
                'clubs' => ['sunderland-freshwater-angling-club'],
                'waters' => [
                    ['name' => 'Top Lake', 'description' => 'Carp and mixed coarse lake.', 'species' => ['carp', 'common-carp', 'mirror-carp', 'bream', 'roach']],
                    ['name' => 'Silksworth Park lakes', 'description' => 'Additional park lakes on the Silksworth complex.', 'species' => ['carp', 'roach', 'perch', 'bream']],
                ],
            ],
            [
                'name' => 'Cleasby Lake',
                'slug' => 'cleasby-lake',
                'overview' => 'Around 6 acres near Cleasby on the outskirts of Darlington, controlled by Darlington Anglers Club. Stocked with rainbow and brown trout plus pike, bream and carp. Catch-and-release.',
                'latitude' => 54.5050,
                'longitude' => -1.6100,
                'address' => 'Cleasby, near Darlington, DL2',
                'url' => 'https://darlingtonanglersclub.org.uk/',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['darlington-anglers-club'],
                'waters' => [
                    ['name' => 'Cleasby Lake', 'description' => 'Trout and coarse stillwater.', 'species' => ['rainbow-trout', 'brown-trout', 'pike', 'bream', 'carp']],
                ],
            ],
            [
                'name' => 'River Tees (Darlington Anglers)',
                'slug' => 'river-tees-darlington-anglers',
                'overview' => 'Darlington Anglers Club middle-Tees fishing from High Coniscliffe westwards to Blackwell, Stapleton and Croft where the Skerne meets the Tees — salmon, sea trout, brown trout, grayling and coarse.',
                'latitude' => 54.5270,
                'longitude' => -1.5900,
                'address' => 'River Tees, Darlington',
                'url' => 'https://darlingtonanglersclub.org.uk/club-waters/',
                'ticket_type' => 'club',
                'is_complex' => true,
                'clubs' => ['darlington-anglers-club'],
                'waters' => [
                    ['name' => 'High Coniscliffe', 'description' => 'Upper club Tees beat.', 'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'grayling']],
                    ['name' => 'Blackwell', 'description' => 'Popular left-bank Tees stretch.', 'species' => ['brown-trout', 'grayling', 'chub', 'barbel']],
                    ['name' => 'Stapleton & Oxney Flatts', 'description' => 'Lower Tees club waters.', 'species' => ['brown-trout', 'grayling', 'chub']],
                ],
            ],
            [
                'name' => 'River Wear (Bishop Auckland)',
                'slug' => 'river-wear-bishop-auckland',
                'overview' => 'Bishop Auckland and District Angling Club controls over 17 miles of Wear fishing with named pools from Witton and Escomb through Newfield/Hunwick to Page Bank and Croxdale.',
                'latitude' => 54.6600,
                'longitude' => -1.6800,
                'address' => 'River Wear, Bishop Auckland',
                'url' => 'https://bishopaucklandanglingclub.com/',
                'ticket_type' => 'mixed',
                'day_ticket_info' => 'Visitor day tickets available via BADAC — see club website for prices and beats.',
                'membership_info' => 'Annual BADAC membership; Town Water and species supplements listed on the club site.',
                'is_complex' => true,
                'clubs' => ['bishop-auckland-district-angling-club'],
                'waters' => [
                    ['name' => 'Witton, Escomb & Bishop Auckland', 'description' => 'Upper BADAC Wear beats.', 'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'grayling']],
                    ['name' => 'Newfield, Hunwick & Willington', 'description' => 'Mid Wear BADAC water.', 'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'grayling']],
                    ['name' => 'Page Bank to Croxdale', 'description' => 'Lower BADAC Wear stretch.', 'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'grayling']],
                ],
            ],
            [
                'name' => 'Mainsforth Pond',
                'slug' => 'mainsforth-pond',
                'overview' => 'Ferryhill and District Angling Club coarse pond — pleasure and specimen fishing for members.',
                'latitude' => 54.6900,
                'longitude' => -1.5300,
                'address' => 'Mainsforth, County Durham',
                'url' => 'https://www.ferryhillanddistrictanglingclub.com/fdacwaters',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['ferryhill-district-angling-club'],
                'waters' => [
                    ['name' => 'Mainsforth Pond', 'description' => 'Club coarse pond.', 'species' => ['carp', 'bream', 'roach', 'perch', 'tench']],
                ],
            ],
            [
                'name' => 'The Tilery',
                'slug' => 'the-tilery',
                'overview' => 'Ferryhill and District Angling Club stillwater with strict hook and bait rules for fish conservation.',
                'latitude' => 54.6800,
                'longitude' => -1.5500,
                'address' => 'Ferryhill, County Durham',
                'url' => 'https://www.ferryhillanddistrictanglingclub.com/fdacwaters',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['ferryhill-district-angling-club'],
                'waters' => [
                    ['name' => 'The Tilery', 'description' => 'Club coarse pond.', 'species' => ['carp', 'bream', 'roach', 'tench']],
                ],
            ],
            [
                'name' => 'Stephensons Lake',
                'slug' => 'stephensons-lake',
                'overview' => 'Hetton Lyons Angling Club match lake in Hetton-le-Hole. Day tickets and members; no night fishing. Carp, tench, bream, crucians and silvers.',
                'latitude' => 54.8200,
                'longitude' => -1.4500,
                'address' => 'Hetton-le-Hole, DH5 0RH',
                'url' => 'https://hlac.co.uk/',
                'ticket_type' => 'mixed',
                'day_ticket_info' => 'Day tickets via hlac.co.uk store. Members also welcome.',
                'is_complex' => false,
                'clubs' => ['hetton-lyons-angling-club'],
                'waters' => [
                    ['name' => 'Stephensons Lake', 'description' => 'Match / pleasure lake.', 'species' => ['carp', 'tench', 'bream', 'crucian', 'roach', 'rudd', 'perch']],
                ],
            ],
            [
                'name' => 'Lyons Lake',
                'slug' => 'lyons-lake',
                'overview' => 'Hetton Lyons members-only carp lake with fish to mid-20s. Booking required before fishing; night fishing allowed.',
                'latitude' => 54.8250,
                'longitude' => -1.4550,
                'address' => 'Hetton-le-Hole, DH5 9DN',
                'url' => 'https://hlac.co.uk/',
                'ticket_type' => 'club',
                'membership_info' => 'HLAC members only — book in before fishing.',
                'is_complex' => false,
                'clubs' => ['hetton-lyons-angling-club'],
                'waters' => [
                    ['name' => 'Lyons Lake', 'description' => 'Specimen carp lake.', 'species' => ['carp', 'common-carp', 'mirror-carp']],
                ],
            ],
            [
                'name' => 'Aycliffe Angling Club Pond',
                'slug' => 'aycliffe-angling-club-pond',
                'overview' => 'Newton Aycliffe club pond for Aycliffe Angling Club members — local coarse fishing in County Durham.',
                'latitude' => 54.6150,
                'longitude' => -1.5750,
                'address' => 'Newton Aycliffe, DL4 2QE',
                'url' => 'https://aycliffeanglingclub1973.godaddysites.com/',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['aycliffe-angling-club'],
                'waters' => [
                    ['name' => 'Aycliffe Pond', 'description' => 'Club coarse pond.', 'species' => ['carp', 'roach', 'bream', 'perch']],
                ],
            ],
            [
                'name' => 'Thornbrough Pond',
                'slug' => 'thornbrough-pond',
                'overview' => 'Corbridge-area stillwater associated with Four in One Angling Club — junior-friendly coarse fishing in the Tyne Valley.',
                'latitude' => 54.9750,
                'longitude' => -2.0150,
                'address' => 'Thornbrough, near Corbridge, NE45',
                'url' => null,
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['four-in-one-angling-club'],
                'waters' => [
                    ['name' => 'Thornbrough Pond', 'description' => 'Mixed coarse pond.', 'species' => ['carp', 'roach', 'bream', 'perch', 'tench']],
                ],
            ],
            [
                'name' => 'Tilcon Ponds',
                'slug' => 'tilcon-ponds',
                'overview' => 'Ryton and District Angling Club ponds alongside Stargate, with further access via Tyne Anglers Alliance membership.',
                'latitude' => 54.9800,
                'longitude' => -1.7600,
                'address' => 'Ryton / Blaydon area, Gateshead',
                'url' => null,
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['ryton-and-district-angling-club'],
                'waters' => [
                    ['name' => 'Tilcon Ponds', 'description' => 'Club coarse ponds.', 'species' => ['carp', 'roach', 'bream', 'perch', 'tench']],
                ],
            ],
            [
                'name' => 'Seldom Seen Ponds',
                'slug' => 'seldom-seen-ponds',
                'overview' => 'Willington & District Angling Club coarse ponds near Newfield and Byers Green, complementing the club’s River Wear game fishing.',
                'latitude' => 54.7000,
                'longitude' => -1.7000,
                'address' => 'Near Newfield / Byers Green, County Durham',
                'url' => 'https://willingtondistrictanglingclub.com/',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['willington-district-angling-club'],
                'waters' => [
                    ['name' => 'Seldom Seen Ponds', 'description' => 'Club coarse ponds.', 'species' => ['carp', 'roach', 'bream', 'tench', 'perch']],
                ],
            ],
            [
                'name' => 'Rothley Lake East',
                'slug' => 'rothley-lake-east',
                'overview' => 'Felling Fly Fishing Club stillwater in Northumberland, regularly stocked with rainbow trout.',
                'latitude' => 55.1500,
                'longitude' => -1.9000,
                'address' => 'Rothley, Northumberland',
                'url' => 'https://www.fellingflyfishers.co.uk/',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['felling-fly-fishing-club'],
                'waters' => [
                    ['name' => 'Rothley Lake East', 'description' => 'Stocked trout stillwater.', 'species' => ['rainbow-trout', 'brown-trout']],
                ],
            ],
            [
                'name' => 'River Wansbeck',
                'slug' => 'river-wansbeck',
                'overview' => 'Wansbeck Angling Association (est. 1907) brown trout fishing on miles of the River Wansbeck in Northumberland.',
                'latitude' => 55.1600,
                'longitude' => -1.6900,
                'address' => 'River Wansbeck, Northumberland',
                'url' => 'https://www.wansbeckanglingassociation.co.uk/',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['wansbeck-angling-association'],
                'waters' => [
                    ['name' => 'Wansbeck Association beats', 'description' => 'Brown trout river fishing.', 'species' => ['brown-trout']],
                ],
            ],
            [
                'name' => 'Pelaw Quarry Pond',
                'slug' => 'pelaw-quarry-pond',
                'overview' => 'Small mixed coarse pond behind the Cock Crow Inn in Hebburn, run by Lakeside Angling Club with Tyne Anglers Alliance access for members.',
                'latitude' => 54.9700,
                'longitude' => -1.5100,
                'address' => 'Hebburn, Tyne and Wear',
                'url' => null,
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['lakeside-angling-club', 'tyne-anglers-alliance'],
                'waters' => [
                    ['name' => 'Pelaw Quarry Pond', 'description' => 'Mixed coarse quarry pond.', 'species' => ['carp', 'roach', 'bream', 'perch', 'tench']],
                ],
            ],
            [
                'name' => 'Derwent Walk Country Park Waters',
                'slug' => 'derwent-walk-country-park-waters',
                'overview' => 'Fishing rights in Derwent Walk Country Park and Derwenthaugh Park leased to Axwell Park and Derwent Valley Angling Association.',
                'latitude' => 54.9450,
                'longitude' => -1.7200,
                'address' => 'Derwent Walk Country Park, Gateshead',
                'url' => 'https://www.apdvaa.co.uk/',
                'ticket_type' => 'club',
                'is_complex' => false,
                'clubs' => ['axwell-park-derwent-valley-angling-association'],
                'waters' => [
                    ['name' => 'Derwent Valley park waters', 'description' => 'Association fishing in the country park.', 'species' => ['roach', 'perch', 'bream', 'carp', 'brown-trout']],
                ],
            ],
            [
                'name' => 'River Tyne (Northumbrian Anglers Federation)',
                'slug' => 'river-tyne-northumbrian-anglers-federation',
                'overview' => 'Northumbrian Anglers Federation Tyne beats including Ovington to Ovingham, Ovingham to Prudhoe and Clara Vale for salmon, sea trout and brown trout (permit rules apply).',
                'latitude' => 54.9700,
                'longitude' => -1.8500,
                'address' => 'River Tyne, Northumberland / Tyne Valley',
                'url' => 'https://northumbrian-angler.co.uk/',
                'ticket_type' => 'mixed',
                'day_ticket_info' => 'Season and visitor permits via northumbrian-angler.co.uk.',
                'is_complex' => true,
                'clubs' => ['northumbrian-anglers-federation'],
                'waters' => [
                    ['name' => 'Ovington to Prudhoe beats', 'description' => 'Federation Tyne game fishing.', 'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout']],
                ],
            ],
            [
                'name' => 'River Coquet (Northumbrian Anglers Federation)',
                'slug' => 'river-coquet-northumbrian-anglers-federation',
                'overview' => 'Classic Northumberland Coquet fishing under Northumbrian Anglers Federation permits — beats from the upper river through Rothbury to Warkworth.',
                'latitude' => 55.3100,
                'longitude' => -1.7200,
                'address' => 'River Coquet, Northumberland',
                'url' => 'https://northumbrian-angler.co.uk/',
                'ticket_type' => 'mixed',
                'day_ticket_info' => 'Season and visitor permits via northumbrian-angler.co.uk.',
                'is_complex' => true,
                'clubs' => ['northumbrian-anglers-federation'],
                'waters' => [
                    ['name' => 'Coquet Federation beats', 'description' => 'Salmon, sea trout and brown trout.', 'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout']],
                ],
            ],
            [
                'name' => 'River Till (Northumbrian Anglers Federation)',
                'slug' => 'river-till-northumbrian-anglers-federation',
                'overview' => 'Pre-book Federation fishing on the River Till for salmon and sea trout — Turvelaws, Weetwood and associated beats.',
                'latitude' => 55.6000,
                'longitude' => -2.0500,
                'address' => 'River Till, Northumberland',
                'url' => 'https://northumbrian-angler.co.uk/',
                'ticket_type' => 'syndicate',
                'membership_info' => 'Salmon permit required; pre-booking on Till beats.',
                'is_complex' => false,
                'clubs' => ['northumbrian-anglers-federation'],
                'waters' => [
                    ['name' => 'Till Federation beats', 'description' => 'Pre-book salmon and sea trout water.', 'species' => ['atlantic-salmon', 'sea-trout']],
                ],
            ],
            [
                'name' => 'Langley Dam',
                'slug' => 'langley-dam',
                'overview' => 'Northumberland coarse fishery near Hexham (NE47), listed among popular North East stillwaters.',
                'latitude' => 54.9400,
                'longitude' => -2.1800,
                'address' => 'Langley, Hexham, NE47 5LD',
                'url' => null,
                'ticket_type' => 'day_ticket',
                'is_complex' => false,
                'clubs' => [],
                'waters' => [
                    ['name' => 'Langley Dam', 'description' => 'Coarse day-ticket stillwater.', 'species' => ['carp', 'bream', 'roach', 'tench', 'perch']],
                ],
            ],
        ];
    }

    /**
     * Link existing directory venues to the clubs that manage or share them.
     *
     * @return array<string, list<string>>
     */
    private function existingLinks(): array
    {
        return [
            'wansbeck-and-cramlington-angling-club' => [
                'qe-ii', 'brenkley-pond', 'horton-grange-lake', 'milkhope-lake',
            ],
            'tyne-anglers-alliance' => [
                'killingworth-lakes', 'throckley-reigh', 'river-tyne-newburn', 'wydon-burn',
                'leazes-park-lake', 'stargate', 'big-waters', 'pelaw-quarry-pond',
            ],
            'big-waters-angling-club' => ['big-waters'],
            'hexham-angling-association' => ['wydon-burn'],
            'ryton-and-district-angling-club' => ['stargate', 'tilcon-ponds'],
            'blyth-freshwater-angling-club' => ['meggies-burn-reservoir'],
            'durham-city-angling-club' => ['brasside-lakes', 'river-wear-durham-city', 'aldin-grange'],
            'chester-le-street-district-angling-club' => [],
            'willington-district-angling-club' => ['seldom-seen-ponds'],
            'leazes-park-angling-club' => ['leazes-park-lake'],
            'tunstall-fly-fishers' => ['tunstall-reservoir'],
            'batleys-fishing-club' => ['batleys-pond'],
            'felling-fly-fishing-club' => ['rothley-lake-east', 'fontburn-reservoir'],
            'wansbeck-angling-association' => ['river-wansbeck'],
        ];
    }
};
