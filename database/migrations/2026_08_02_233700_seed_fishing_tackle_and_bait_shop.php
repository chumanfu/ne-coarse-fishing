<?php

use App\Models\TackleShop;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        TackleShop::query()->updateOrCreate(
            ['slug' => 'fishing-tackle-and-bait'],
            [
                'name' => 'Fishing Tackle and Bait',
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
        );
    }

    public function down(): void
    {
        TackleShop::query()->where('slug', 'fishing-tackle-and-bait')->delete();
    }
};
