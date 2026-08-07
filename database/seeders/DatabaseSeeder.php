<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\FishingSession;
use App\Models\MatchReport;
use App\Models\SessionCatch;
use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect(['angler', 'fishery_manager', 'club_owner', 'tackle_shop_owner', 'super_admin'])
            ->mapWithKeys(fn (string $role) => [$role => Role::findOrCreate($role)]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@nefishing.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles([$roles['super_admin']]);

        $manager = User::query()->updateOrCreate(
            ['email' => 'manager@nefishing.test'],
            [
                'name' => 'Aldin Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->syncRoles([$roles['fishery_manager'], $roles['angler']]);

        $clubOwner = User::query()->updateOrCreate(
            ['email' => 'club@nefishing.test'],
            [
                'name' => 'Club Owner',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $clubOwner->syncRoles([$roles['club_owner'], $roles['angler']]);

        $shopOwner = User::query()->updateOrCreate(
            ['email' => 'shop@nefishing.test'],
            [
                'name' => 'Tackle Shop Owner',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $shopOwner->syncRoles([$roles['tackle_shop_owner'], $roles['angler']]);

        $angler = User::query()->updateOrCreate(
            ['email' => 'angler@nefishing.test'],
            [
                'name' => 'Chris Angler',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $angler->syncRoles([$roles['angler']]);

        $speciesNames = [
            'Carp', 'Tench', 'Bream', 'Roach', 'Rudd', 'Perch', 'Pike', 'Chub', 'Barbel', 'Ide', 'F1', 'Crucian',
        ];

        $species = collect($speciesNames)->mapWithKeys(function (string $name) {
            $model = Species::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );

            return [$name => $model];
        });

        $aldin = Venue::query()->updateOrCreate(
            ['slug' => 'aldin-grange'],
            [
                'user_id' => $manager->id,
                'manager_id' => $manager->id,
                'name' => 'Aldin Grange Lakes',
                'overview' => "Popular Durham complex with a mix of match and pleasure waters. Good parking, on-site café on weekends, and a steady head of carp and silverfish.\n\nIdeal for a first look at North East commercial-style fishing without leaving County Durham.",
                'latitude' => 54.7912,
                'longitude' => -1.6208,
                'address' => 'Aldin Grange Farm, Bearpark, Durham DH7 7AR',
                'url' => 'https://aldingrangelakes.co.uk/',
                'directions' => "From Durham follow the A691 towards Consett, then local signs for Bearpark / Aldin Grange. Car park is gravel and can be soft after heavy rain — stick to the marked bays.",
                'day_ticket_info' => "Adult day tickets from the bailiff on the bank.\nJuniors and OAPs discounted.\nNight fishing by prior arrangement only.",
                'membership_info' => 'No waiting list currently. Annual club tickets available from the on-site cabin.',
                'ticket_type' => 'mixed',
                'opening_times' => 'Dawn until dusk for day tickets. Gates locked overnight except for booked night sessions.',
                'season_info' => 'Open year-round. Close-season does not apply to these stillwaters.',
                'tactics_guide' => "Method feeder and wafters score heavily on the Match Lake.\nOn the Specimen Lake, zig rigs in warmer months and solid bags into margins after dark.\nKeep the feed tight — these fish see a lot of anglers.",
                'is_complex' => true,
                'is_approved' => true,
                'manager_verified' => true,
            ]
        );

        $matchLake = Water::query()->updateOrCreate(
            ['venue_id' => $aldin->id, 'name' => 'Match Lake'],
            [
                'description' => 'Island-feature match water with even pegging and good silverfish sport.',
                'peg_count' => 28,
                'depth_info' => '4–7ft',
                'sort_order' => 1,
            ]
        );
        $matchLake->species()->sync([
            $species['Carp']->id,
            $species['F1']->id,
            $species['Bream']->id,
            $species['Roach']->id,
            $species['Perch']->id,
        ]);

        $specimenLake = Water::query()->updateOrCreate(
            ['venue_id' => $aldin->id, 'name' => 'Specimen Lake'],
            [
                'description' => 'Larger carp water with reed-lined margins and two islands.',
                'peg_count' => 16,
                'depth_info' => '6–12ft',
                'sort_order' => 2,
            ]
        );
        $specimenLake->species()->sync([
            $species['Carp']->id,
            $species['Tench']->id,
            $species['Pike']->id,
        ]);

        $derwent = Venue::query()->updateOrCreate(
            ['slug' => 'derwent-reservoir'],
            [
                'user_id' => $angler->id,
                'name' => 'Derwent Reservoir Banks',
                'overview' => 'Expansive Northumbrian reservoir fishing for pike, perch and the odd specimen carp along accessible roadside banks.',
                'latitude' => 54.8605,
                'longitude' => -1.9784,
                'address' => 'Derwent Reservoir, near Edmundbyers, DH8 9TT',
                'url' => 'https://www.watersideparksuk.com/park/derwent/fishing/',
                'directions' => 'Use the main visitor centre car parks. Walk the marked paths to the fishing banks — do not block estate access tracks.',
                'day_ticket_info' => 'Day tickets from the visitor centre / online where advertised.',
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'Daylight hours; check site notices for seasonal gate times.',
                'season_info' => 'Follow posted reservoir rules and any temporary closures.',
                'tactics_guide' => 'Lure fishing for pike along windward banks. Float-fished worm and maggot for perch in the deeper holes.',
                'is_complex' => false,
                'is_approved' => true,
                'manager_verified' => false,
            ]
        );

        $derwentWater = Water::query()->updateOrCreate(
            ['venue_id' => $derwent->id, 'name' => 'Main Basin Banks'],
            [
                'description' => 'Roadside and path-access stretches on the main reservoir.',
                'peg_count' => null,
                'depth_info' => 'Varies — deep drop-offs close in on some pegs',
                'sort_order' => 1,
            ]
        );
        $derwentWater->species()->sync([
            $species['Pike']->id,
            $species['Perch']->id,
            $species['Roach']->id,
            $species['Carp']->id,
        ]);

        MatchReport::query()->updateOrCreate(
            ['venue_id' => $aldin->id, 'title' => 'Sunday open — Match Lake'],
            [
                'water_id' => $matchLake->id,
                'user_id' => $manager->id,
                'body' => "Winning weight 98lb 4oz from peg 14 on the method.\nSilvers made up a big part of the frame — soft pellets and groundbait in the edge late on.",
                'published_at' => now()->subDays(3),
            ]
        );

        Announcement::query()->updateOrCreate(
            ['venue_id' => $aldin->id, 'title' => 'Spring stocking — Specimen Lake'],
            [
                'user_id' => $manager->id,
                'type' => 'stocking',
                'body' => 'Twenty upper-double mirrors introduced overnight. Please give them a week before heavy baiting on the new stock pegs.',
                'published_at' => now()->subDays(10),
            ]
        );

        $session = FishingSession::query()->updateOrCreate(
            [
                'user_id' => $angler->id,
                'venue_id' => $aldin->id,
                'fished_at' => now()->subDays(5)->toDateString(),
            ],
            [
                'water_id' => $matchLake->id,
                'duration_hours' => 6,
                'weather' => 'Overcast, light SW breeze',
                'peg_number' => '18',
                'commentary' => 'Steady day on the short pellet waggler. Fish moved tighter to the island after lunch.',
            ]
        );

        SessionCatch::query()->updateOrCreate(
            [
                'fishing_session_id' => $session->id,
                'species_id' => $species['Carp']->id,
            ],
            [
                'weight_lb' => 8.5,
                'bait' => '6mm banded pellet',
                'quantity' => 4,
            ]
        );

        SessionCatch::query()->updateOrCreate(
            [
                'fishing_session_id' => $session->id,
                'species_id' => $species['F1']->id,
            ],
            [
                'weight_lb' => null,
                'bait' => 'Soft pellet',
                'quantity' => 9,
            ]
        );
    }
}
