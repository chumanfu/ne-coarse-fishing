<?php

use App\Models\Club;
use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

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
            ['slug' => 'darlington-anglers-club'],
            [
                'name' => 'Darlington Anglers Club',
                'url' => 'https://darlingtonanglersclub.org.uk/',
                'facebook_url' => 'https://www.facebook.com/darlingtonanglers/',
                'overview' => 'Established 1894. Around nine miles of River Tees fishing from High Coniscliffe to Croft, plus Cleasby Lake stocked with trout and coarse fish. Catch-and-release on all club waters.',
                'town' => 'Darlington',
                'address' => null,
                'phone' => null,
                'is_featured' => true,
                'sort_order' => 65,
                'is_published' => true,
                'logo_path' => File::exists(public_path('images/clubs/darlington-anglers-club.png'))
                    ? 'images/clubs/darlington-anglers-club.png'
                    : null,
            ],
        );

        $cleasby = Venue::query()->updateOrCreate(
            ['slug' => 'cleasby-lake'],
            [
                'user_id' => $admin->id,
                'name' => 'Cleasby Lake',
                'overview' => 'Around 6 acres near Cleasby on the outskirts of Darlington, controlled by Darlington Anglers Club. Stocked with rainbow and brown trout plus pike, bream and carp. Catch-and-release; no driving around the lake (landowner rule).',
                'latitude' => 54.5050,
                'longitude' => -1.6100,
                'address' => 'Cleasby, near Darlington, DL2',
                'url' => 'https://darlingtonanglersclub.org.uk/',
                'ticket_type' => 'club',
                'membership_info' => 'Membership via Darlington Anglers Club / Clubmate. See club website for current prices and renewals.',
                'season_info' => 'No close season on Cleasby Lake. Club pike fishing on the lake is 1 October–28 February. Catch-and-release on all species.',
                'is_complex' => false,
                'is_approved' => true,
                'manager_verified' => false,
            ],
        );

        $cleasbyWater = Water::query()->updateOrCreate(
            [
                'venue_id' => $cleasby->id,
                'name' => 'Cleasby Lake',
            ],
            [
                'description' => 'Trout and coarse stillwater near Cleasby. Vehicles must not drive around the lake.',
                'sort_order' => 1,
            ],
        );
        $cleasbyWater->species()->sync($this->speciesIds([
            'rainbow-trout',
            'brown-trout',
            'pike',
            'bream',
            'carp',
        ]));

        $tees = Venue::query()->updateOrCreate(
            ['slug' => 'river-tees-darlington-anglers'],
            [
                'user_id' => $admin->id,
                'name' => 'River Tees (Darlington Anglers)',
                'overview' => 'Darlington Anglers Club middle-Tees fishing from High Coniscliffe westwards through Manfield, Cleasby, The Holmes, Blackwell, Stapleton and Nags Head / Oxney Flatts to Croft where the Skerne meets the Tees — salmon, sea trout, brown trout, grayling and coarse. Catch-and-release on all club waters.',
                'latitude' => 54.5270,
                'longitude' => -1.5900,
                'address' => 'River Tees, Darlington',
                'url' => 'https://darlingtonanglersclub.org.uk/club-waters/',
                'ticket_type' => 'club',
                'membership_info' => 'Membership via Darlington Anglers Club / Clubmate. See club website for current prices and renewals.',
                'season_info' => 'Follow Environment Agency North East rod fishing byelaws. Club pike fishing on river and lake is 1 October–28 February. Catch-and-release on all species.',
                'is_complex' => true,
                'is_approved' => true,
                'manager_verified' => false,
            ],
        );

        // Split the older combined stretch into the named club beats below.
        $legacy = Water::query()
            ->where('venue_id', $tees->id)
            ->where('name', 'Stapleton & Oxney Flatts')
            ->first();

        if ($legacy) {
            $legacy->update([
                'name' => 'Stapleton',
                'description' => 'About 1½ miles of the Yorkshire (right) bank between Stapleton and Nag’s Head corner.',
                'sort_order' => 5,
            ]);
        }

        $teesBeats = [
            [
                'name' => 'High Coniscliffe',
                'description' => 'About 1½ miles of the northern (left) bank between High Coniscliffe and Merrybent — deep pools and gravelly runs with wild brown trout, grayling and chub.',
                'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'grayling', 'chub'],
            ],
            [
                'name' => 'Manfield & Cleasby',
                'description' => 'Tees club water around Manfield and Cleasby; parking and access details on the club waters page.',
                'species' => ['brown-trout', 'grayling', 'chub', 'barbel', 'pike'],
            ],
            [
                'name' => 'The Holmes',
                'description' => 'About 1¼ miles of the Yorkshire (right) bank between Cleasby Deeps and Stapleton Corner, including Blackwell Bridge Pool and The Crusher Pool.',
                'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'barbel', 'chub', 'pike'],
            ],
            [
                'name' => 'Blackwell',
                'description' => 'About 1½ miles of the Durham (left) bank between Blackwell Road Bridge and Blackwell Pumping Station — the club’s most popular stretch.',
                'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'grayling', 'dace', 'chub', 'barbel', 'pike'],
            ],
            [
                'name' => 'Stapleton',
                'description' => 'About 1½ miles of the Yorkshire (right) bank between Stapleton and Nag’s Head corner.',
                'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'grayling', 'dace', 'chub', 'barbel', 'pike'],
            ],
            [
                'name' => 'Nags Head & Oxney Flatts',
                'description' => 'About two miles of the Durham (left) bank between Blackwell Pumping Station and the mouth of the River Skerne at Croft — strong pegs for trout, grayling and coarse.',
                'species' => ['atlantic-salmon', 'sea-trout', 'brown-trout', 'grayling', 'dace', 'chub', 'barbel', 'pike', 'bream'],
            ],
        ];

        foreach ($teesBeats as $index => $beat) {
            $species = $beat['species'];
            unset($beat['species']);

            $water = Water::query()->updateOrCreate(
                [
                    'venue_id' => $tees->id,
                    'name' => $beat['name'],
                ],
                [
                    ...$beat,
                    'sort_order' => $index + 1,
                ],
            );

            $water->species()->sync($this->speciesIds($species));
        }

        $cleasby->clubs()->syncWithoutDetaching([$club->id]);
        $tees->clubs()->syncWithoutDetaching([$club->id]);
    }

    public function down(): void
    {
        Club::query()
            ->where('slug', 'darlington-anglers-club')
            ->update([
                'logo_path' => null,
                'facebook_url' => null,
            ]);
    }

    /**
     * @param  list<string>  $slugs
     * @return list<int>
     */
    private function speciesIds(array $slugs): array
    {
        return Species::query()
            ->whereIn('slug', $slugs)
            ->pluck('id')
            ->all();
    }
};
