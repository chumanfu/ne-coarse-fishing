<?php

use App\Models\TackleShop;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->shops() as $shop) {
            TackleShop::query()->updateOrCreate(
                ['slug' => $shop['slug']],
                $shop,
            );
        }
    }

    public function down(): void
    {
        TackleShop::query()
            ->whereIn('slug', array_column($this->shops(), 'slug'))
            ->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function shops(): array
    {
        return [
            [
                'name' => 'Billy\'s Fishing Tackle',
                'slug' => 'billys-fishing-tackle',
                'url' => 'https://billysfishing.co.uk/',
                'overview' => 'Independent North Shields shop covering coarse, carp, sea and game. A go-to for bait and terminal tackle before a day on the Tyne coast or local lakes.',
                'town' => 'North Shields',
                'address' => '28 Saville Street West, North Shields, NE29 6QR',
                'phone' => '0191 259 6262',
                'location_type' => 'local',
                'is_featured' => true,
                'sort_order' => 10,
                'is_published' => true,
            ],
            [
                'name' => 'Rutherford\'s Angling',
                'slug' => 'rutherfords-angling',
                'url' => 'https://www.rutherfordsangling.co.uk/',
                'overview' => 'Established sea-angling specialist in Sunderland with brands for shore, pier and boat fishing, plus repairs and bait.',
                'town' => 'Sunderland',
                'address' => '125 Roker Avenue, Sunderland, SR6 0HL',
                'phone' => '0191 565 4183',
                'location_type' => 'hybrid',
                'is_featured' => true,
                'sort_order' => 20,
                'is_published' => true,
            ],
            [
                'name' => 'North East Tackle',
                'slug' => 'north-east-tackle',
                'url' => 'https://www.northeasttackle.co.uk/',
                'overview' => 'Large North East retailer with stores in the Sunderland and Hartlepool area plus a substantial online catalogue across sea, carp and coarse.',
                'town' => 'Sunderland & Hartlepool',
                'address' => null,
                'phone' => null,
                'location_type' => 'hybrid',
                'is_featured' => true,
                'sort_order' => 30,
                'is_published' => true,
            ],
            [
                'name' => 'Bagnall and Kirkwood',
                'slug' => 'bagnall-and-kirkwood',
                'url' => 'https://bagnallandkirkwood.co.uk/',
                'overview' => 'Long-standing Newcastle fishing and shooting retailer stocking coarse, sea and fly gear with parking at the Rothbury Terrace store.',
                'town' => 'Newcastle upon Tyne',
                'address' => '220 Rothbury Terrace, Newcastle upon Tyne, NE6 5DE',
                'phone' => '0191 232 5873',
                'location_type' => 'local',
                'is_featured' => true,
                'sort_order' => 40,
                'is_published' => true,
            ],
            [
                'name' => 'Angling Direct Stockton',
                'slug' => 'angling-direct-stockton',
                'url' => 'https://www.anglingdirect.co.uk/storelocator/stockton',
                'overview' => 'Large AD superstore near the Tees with carp, coarse, predator, fly and sea departments, reel servicing and local fishery advice.',
                'town' => 'Stockton-on-Tees',
                'address' => 'Unit 4, Portrack Trade Park, Cheltenham Road, Stockton-on-Tees, TS18 2AD',
                'phone' => null,
                'location_type' => 'local',
                'is_featured' => true,
                'sort_order' => 50,
                'is_published' => true,
            ],
            [
                'name' => 'AD Tackle',
                'slug' => 'ad-tackle',
                'url' => 'https://adtackle.co.uk/',
                'overview' => 'Family-run Wallsend shop (A & D Tackle) stocking sea, boat and coarse gear plus fresh and frozen bait for Tyneside anglers.',
                'town' => 'Wallsend',
                'address' => '13 Border Road, Wallsend, Newcastle upon Tyne, NE28 6RX',
                'phone' => '0191 389 7625',
                'location_type' => 'hybrid',
                'is_featured' => true,
                'sort_order' => 55,
                'is_published' => true,
            ],
            [
                'name' => 'Wallys Fishing Tackle',
                'slug' => 'wallys-fishing-tackle',
                'url' => 'http://wallysfishingtackle.co.uk/',
                'overview' => 'Darlington tackle shop run by anglers: buy, sell and trade gear with practical advice for local waters.',
                'town' => 'Darlington',
                'address' => 'Darlington, County Durham',
                'phone' => '07572 718958',
                'location_type' => 'local',
                'is_featured' => false,
                'sort_order' => 60,
                'is_published' => true,
            ],
            [
                'name' => 'The Anglers Lodge',
                'slug' => 'the-anglers-lodge',
                'url' => 'https://www.anglers-lodge.co.uk/',
                'overview' => 'Family-run tackle and fly-tying shop at Jubilee Lakes near Darlington, handy for Teesside, Teesdale and North Yorkshire anglers.',
                'town' => 'Jubilee Lakes, Darlington',
                'address' => 'Jubilee Lakes, near Darlington',
                'phone' => null,
                'location_type' => 'hybrid',
                'is_featured' => false,
                'sort_order' => 70,
                'is_published' => true,
            ],
            [
                'name' => 'Angling Direct',
                'slug' => 'angling-direct',
                'url' => 'https://www.anglingdirect.co.uk/',
                'overview' => 'UK\'s largest specialist tackle retailer with a huge online range and stores nationwide — including Stockton-on-Tees and Washington in the North East.',
                'town' => null,
                'address' => null,
                'phone' => null,
                'location_type' => 'online',
                'is_featured' => true,
                'sort_order' => 100,
                'is_published' => true,
            ],
            [
                'name' => 'Fishdeal',
                'slug' => 'fishdeal',
                'url' => 'https://www.fishdeal.co.uk/',
                'overview' => 'Major online tackle store with daily deals across carp, coarse, predator, fly and sea — popular for discounted rods, reels and terminal gear.',
                'town' => null,
                'address' => null,
                'phone' => null,
                'location_type' => 'online',
                'is_featured' => true,
                'sort_order' => 110,
                'is_published' => true,
            ],
            [
                'name' => 'Fishing Tackle and Bait',
                'slug' => 'fishing-tackle-and-bait',
                'url' => 'https://www.fishingtackleandbait.co.uk/',
                'overview' => 'Large UK online tackle retailer with a bricks-and-mortar shop in Enniskillen, stocking coarse, carp, sea and game gear plus bait with nationwide delivery.',
                'town' => 'Enniskillen',
                'address' => 'Old Scotch Stores, 1 Sligo Road, Enniskillen, Co. Fermanagh, BT74 7JY',
                'phone' => '028 6632 2008',
                'location_type' => 'hybrid',
                'is_featured' => true,
                'sort_order' => 115,
                'is_published' => true,
            ],
            [
                'name' => 'Willy Worms',
                'slug' => 'willy-worms',
                'url' => 'https://willyworms.co.uk/',
                'overview' => 'Specialist live-bait supplier and tackle shop near Selby, stocking fresh maggots, worms and casters plus match, carp and predator gear with next-day UK delivery.',
                'town' => 'Selby',
                'address' => 'Baxter Hall Farm, Long Drax, Selby, YO8 8NH',
                'phone' => '01757 618 549',
                'location_type' => 'hybrid',
                'is_featured' => true,
                'sort_order' => 117,
                'is_published' => true,
            ],
            [
                'name' => 'Total Fishing Tackle',
                'slug' => 'total-fishing-tackle',
                'url' => 'https://www.total-fishing-tackle.com/',
                'overview' => 'Well-known UK online retailer stocking leading brands for match, carp, predator and pleasure fishing with regular promotions.',
                'town' => null,
                'address' => null,
                'phone' => null,
                'location_type' => 'online',
                'is_featured' => true,
                'sort_order' => 120,
                'is_published' => true,
            ],
            [
                'name' => 'Fishing Megastore',
                'slug' => 'fishing-megastore',
                'url' => 'https://www.fishingmegastore.com/',
                'overview' => 'Large UK online tackle shop covering coarse, carp, sea and game with a deep brand list and mail-order service.',
                'town' => null,
                'address' => null,
                'phone' => null,
                'location_type' => 'online',
                'is_featured' => true,
                'sort_order' => 130,
                'is_published' => true,
            ],
            [
                'name' => 'Nathans of Derby',
                'slug' => 'nathans-of-derby',
                'url' => 'https://www.nathansofderby.com/',
                'overview' => 'Long-established UK online and mail-order tackle retailer popular for coarse and match gear delivered nationwide.',
                'town' => null,
                'address' => null,
                'phone' => null,
                'location_type' => 'online',
                'is_featured' => false,
                'sort_order' => 140,
                'is_published' => true,
            ],
        ];
    }
};
