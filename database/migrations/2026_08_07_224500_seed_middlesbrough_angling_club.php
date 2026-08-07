<?php

use App\Models\Club;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Club::query()->updateOrCreate(
            ['slug' => 'middlesbrough-angling-club'],
            [
                'name' => 'Middlesbrough Angling Club',
                'url' => 'https://www.middlesbroughanglingclub.co.uk/',
                'overview' => 'Teesside club (M.A.C.) with stillwaters including Marske Reservoir, Hutton Rudby Ponds, Lockwood Beck and Scaling Dam, plus River Tees, Leven and Swale stretches for members.',
                'town' => 'Middlesbrough',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 160,
                'is_published' => true,
            ],
        );
    }

    public function down(): void
    {
        Club::query()->where('slug', 'middlesbrough-angling-club')->delete();
    }
};
