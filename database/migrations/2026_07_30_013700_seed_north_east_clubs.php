<?php

use App\Models\Club;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->clubs() as $club) {
            Club::query()->updateOrCreate(
                ['slug' => $club['slug']],
                $club,
            );
        }
    }

    public function down(): void
    {
        Club::query()
            ->whereIn('slug', array_column($this->clubs(), 'slug'))
            ->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function clubs(): array
    {
        return [
            [
                'name' => 'Wansbeck and Cramlington Angling Club',
                'slug' => 'wansbeck-and-cramlington-angling-club',
                'url' => 'https://www.wacac.me.uk/',
                'overview' => 'Also known as WACAC. Coarse club covering lakes on Blagdon Estate and affiliated Tyne Anglers Alliance waters including QE II, Brenkley, Horton Grange and Milkhope.',
                'town' => 'Cramlington',
                'address' => null,
                'phone' => '07729 784660',
                'is_featured' => true,
                'sort_order' => 10,
                'is_published' => true,
            ],
            [
                'name' => 'Tyne Anglers Alliance',
                'slug' => 'tyne-anglers-alliance',
                'url' => 'https://thetaa.co.uk/',
                'overview' => 'Alliance of Tyneside clubs looking after shared waters such as Killingworth Lakes, Throckley Reigh, River Tyne at Newburn/Ryton and Wydon Burn.',
                'town' => 'Tyneside',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 20,
                'is_published' => true,
            ],
            [
                'name' => 'Big Waters Angling Club',
                'slug' => 'big-waters-angling-club',
                'url' => 'https://bwac.club/',
                'overview' => 'Newcastle-based coarse club promoting angling in the North East, with access to Big Waters nature reserve lakes and Tyne Anglers Alliance venues.',
                'town' => 'Newcastle upon Tyne',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 30,
                'is_published' => true,
            ],
            [
                'name' => 'Hexham Angling Association',
                'slug' => 'hexham-angling-association',
                'url' => null,
                'overview' => 'Manages Wydon Burn / Wydon Water near Hexham. Advance booking required for Tyne Anglers Alliance complimentary tickets.',
                'town' => 'Hexham',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 40,
                'is_published' => true,
            ],
            [
                'name' => 'Ryton and District Angling Club',
                'slug' => 'ryton-and-district-angling-club',
                'url' => null,
                'overview' => 'Gateshead-area club with Stargate and Tilcon Ponds plus Tyne Anglers Alliance waters on the Tyne corridor.',
                'town' => 'Ryton',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 50,
                'is_published' => true,
            ],
            [
                'name' => 'Blyth Freshwater Angling Club',
                'slug' => 'blyth-freshwater-angling-club',
                'url' => null,
                'overview' => 'Blyth FAC looks after Meggies Burn Reservoir in South Newsham Country Park and caters for local freshwater anglers.',
                'town' => 'Blyth',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 60,
                'is_published' => true,
            ],
            [
                'name' => 'Durham City Angling Club',
                'slug' => 'durham-city-angling-club',
                'url' => 'https://www.durhamanglers.co.uk/',
                'overview' => 'Long-established club with River Wear stretches around Durham and the Brasside stillwater complex for coarse and game anglers.',
                'town' => 'Durham',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 70,
                'is_published' => true,
            ],
            [
                'name' => 'Chester-le-Street & District Angling Club',
                'slug' => 'chester-le-street-district-angling-club',
                'url' => 'https://www.chesterlestreetangling.org.uk/',
                'overview' => 'Historic club (est. 1935) managing early non-tidal River Wear beats for salmon, sea trout and associated member fishing.',
                'town' => 'Chester-le-Street',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 80,
                'is_published' => true,
            ],
            [
                'name' => 'Willington & District Angling Club',
                'slug' => 'willington-district-angling-club',
                'url' => 'https://willingtondistrictanglingclub.com/',
                'overview' => 'Wear Valley club with game fishing on the River Wear plus coarse ponds at Seldom Seen near Newfield and Byers Green.',
                'town' => 'Willington',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 90,
                'is_published' => true,
            ],
            [
                'name' => 'Leazes Park Angling Club',
                'slug' => 'leazes-park-angling-club',
                'url' => null,
                'overview' => 'City-centre Newcastle club fishing Leazes Park lake (roach, tench, bream and carp) and affiliated Tyne Anglers Alliance waters.',
                'town' => 'Newcastle upon Tyne',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 100,
                'is_published' => true,
            ],
            [
                'name' => 'Tunstall Fly Fishers',
                'slug' => 'tunstall-fly-fishers',
                'url' => null,
                'overview' => 'Fly club associated with Tunstall Reservoir near Wolsingham in Weardale for trout fishing.',
                'town' => 'Wolsingham',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 110,
                'is_published' => true,
            ],
            [
                'name' => 'Batleys Fishing Club CIC',
                'slug' => 'batleys-fishing-club',
                'url' => null,
                'overview' => 'Community interest club running Batley\'s Pond near Durham — a mixed fishery with carp, silvers and good access.',
                'town' => 'Durham',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 120,
                'is_published' => true,
            ],
            [
                'name' => 'Felling Fly Fishing Club',
                'slug' => 'felling-fly-fishing-club',
                'url' => null,
                'overview' => 'North East fly club with extensive river beats on the North Tyne, Coquet, Wear and Till, plus stocked stillwaters.',
                'town' => 'Gateshead',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 130,
                'is_published' => true,
            ],
            [
                'name' => 'Wansbeck Angling Association',
                'slug' => 'wansbeck-angling-association',
                'url' => 'https://www.wansbeckanglingassociation.co.uk/',
                'overview' => 'Historic association (est. 1907) offering miles of brown trout fishing on the River Wansbeck in Northumberland.',
                'town' => 'Northumberland',
                'address' => null,
                'phone' => null,
                'is_featured' => false,
                'sort_order' => 140,
                'is_published' => true,
            ],
        ];
    }
};
