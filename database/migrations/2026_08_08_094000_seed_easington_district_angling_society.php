<?php

use App\Models\Club;
use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $admin = User::query()
            ->where('email', 'admin@nefishing.test')
            ->first() ?? User::query()->first();

        if (! $admin) {
            return;
        }

        $club = Club::query()->updateOrCreate(
            ['slug' => 'easington-district-angling-society'],
            [
                'name' => 'Easington District Angling Society',
                'url' => 'https://www.easingtondistrictanglingsociety.co.uk/',
                'facebook_url' => 'https://www.facebook.com/EasingtonDistrictAC',
                'overview' => "Founded in 1976 as Peterlee Angling Club and renamed in 1979. Friendly East Durham club of around 120 members, owning Wellfield Lake at Moor Lane, Wingate — mixed coarse fishing with disabled-access pegs and on-site parking.",
                'town' => 'Wingate',
                'address' => 'Moor Lane, Wingate, County Durham, TS28 5BA',
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 165,
                'is_published' => true,
            ],
        );

        $venue = Venue::query()->updateOrCreate(
            ['slug' => 'wellfield-lake'],
            [
                'user_id' => $admin->id,
                'name' => 'Wellfield Lake',
                'overview' => "Easington District Angling Society’s club lake on Moor Lane, Wingate. About 3.5 acres of water in an 8-acre site, with around 30 pegs including disabled spots near the car park.\n\nMixed stock of carp, bream, tench, perch, roach, rudd, crucian, gudgeon and ide. Members only — no day tickets.",
                'latitude' => 54.7335,
                'longitude' => -1.3855,
                'address' => 'Moor Lane, Wingate, County Durham, TS28 5BA',
                'url' => 'https://www.easingtondistrictanglingsociety.co.uk/',
                'facebook_url' => 'https://www.facebook.com/EasingtonDistrictAC',
                'directions' => 'Moor Lane, Wingate (TS28 5BA). On-site club parking with short walk to pegs; disabled pegs close to the car park.',
                'day_ticket_info' => 'No day tickets — EDAS membership required.',
                'membership_info' => "Membership runs 1 January–31 December. Typical fees from the club site: Senior £15 (+£10 joining), OAP/Disabled £7.50 (+£5), Junior under 16 £7.50 (+£5), Junior under 10 £3 (+£3). Confirm current prices on the club website.",
                'ticket_type' => 'club',
                'opening_times' => 'Member access subject to club rules — see EDAS notices.',
                'season_info' => 'Year-round subject to club rules.',
                'is_complex' => false,
                'is_approved' => true,
                'manager_verified' => false,
            ],
        );

        $water = Water::query()->updateOrCreate(
            [
                'venue_id' => $venue->id,
                'name' => 'Wellfield Lake',
            ],
            [
                'description' => '3.5-acre mixed coarse lake with ~30 pegs including disabled access near parking.',
                'peg_count' => 30,
                'sort_order' => 1,
            ],
        );

        $speciesIds = Species::query()
            ->whereIn('slug', ['carp', 'bream', 'tench', 'perch', 'roach', 'rudd', 'crucian', 'gudgeon', 'ide'])
            ->pluck('id')
            ->all();

        if ($speciesIds !== []) {
            $water->species()->sync($speciesIds);
        }

        $venue->clubs()->syncWithoutDetaching([$club->id]);
    }

    public function down(): void
    {
        $venue = Venue::query()->where('slug', 'wellfield-lake')->first();

        if ($venue) {
            $venue->clubs()->detach();
            $venue->waters()->each(function (Water $water): void {
                $water->species()->detach();
                $water->delete();
            });
            $venue->delete();
        }

        Club::query()->where('slug', 'easington-district-angling-society')->delete();
    }
};
