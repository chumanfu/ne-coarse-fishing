<?php

use App\Models\TackleShop;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        TackleShop::query()->updateOrCreate(
            ['slug' => 'ad-tackle'],
            [
                'name' => 'AD Tackle',
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
        );
    }

    public function down(): void
    {
        TackleShop::query()->where('slug', 'ad-tackle')->delete();
    }
};
