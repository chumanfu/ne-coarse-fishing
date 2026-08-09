<?php

use App\Models\TackleShop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        TackleShop::query()->updateOrCreate(
            ['slug' => 'willy-worms'],
            [
                'name' => 'Willy Worms',
                'url' => 'https://willyworms.co.uk/',
                'overview' => 'Specialist live-bait supplier and tackle shop near Selby, stocking fresh maggots, worms and casters plus match, carp and predator gear with next-day UK delivery.',
                'town' => 'Selby',
                'address' => 'Baxter Hall Farm, Long Drax, Selby, YO8 8NH',
                'phone' => '01757 618 549',
                'location_type' => 'hybrid',
                'is_featured' => true,
                'sort_order' => 117,
                'is_published' => true,
                'logo_path' => File::exists(public_path('images/tackle-shops/willy-worms.png'))
                    ? 'images/tackle-shops/willy-worms.png'
                    : null,
            ],
        );
    }

    public function down(): void
    {
        TackleShop::query()->where('slug', 'willy-worms')->delete();
    }
};
