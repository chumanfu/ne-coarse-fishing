<?php

use App\Models\TackleShop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        TackleShop::query()->updateOrCreate(
            ['slug' => 'birds-tackle'],
            [
                'name' => 'Birds Tackle',
                'url' => 'https://www.birdstackle.co.uk/',
                'overview' => 'Ipswich tackle and bait shop with nationwide online delivery, stocking coarse, carp, specialist, predator and sea gear plus free shipping over £50.',
                'town' => 'Ipswich',
                'address' => 'Coal Yard, Gipping Road, Ipswich, Suffolk, IP6 0JB',
                'phone' => '01473 830 683',
                'location_type' => 'hybrid',
                'is_featured' => true,
                'sort_order' => 118,
                'is_published' => true,
                'logo_path' => File::exists(public_path('images/tackle-shops/birds-tackle.png'))
                    ? 'images/tackle-shops/birds-tackle.png'
                    : null,
            ],
        );
    }

    public function down(): void
    {
        TackleShop::query()->where('slug', 'birds-tackle')->delete();
    }
};
