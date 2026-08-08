<?php

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

        $venue = Venue::query()->updateOrCreate(
            ['slug' => 'burradon-pond'],
            [
                'user_id' => $admin->id,
                'name' => 'Burradon Pond',
                'overview' => "Small stillwater on the former Burradon Colliery site between Burradon and Camperdown, North Tyneside — beside the old Seaton Burn / Killingworth waggonway corridor.\n\nLocal anglers have described it as a mixed pleasure water with carp (historically to mid-teens) and silvers. Access and ownership are not commercially managed like a day-ticket complex, so confirm current rules and rights before fishing.",
                'latitude' => 55.044635,
                'longitude' => -1.572892,
                'address' => 'Burradon / Camperdown, North Tyneside (near Cramlington), NE23',
                'url' => null,
                'directions' => 'Off the Burradon–Camperdown area near Kirklands and the historic waggonway paths. Limited formal parking — park considerately and avoid blocking residents or farm access.',
                'day_ticket_info' => 'Historically reported as free fishing with a valid Environment Agency rod licence, but this is not a managed commercial fishery. Check current landowner/council access before travelling — do not assume free access.',
                'membership_info' => null,
                'ticket_type' => 'day_ticket',
                'opening_times' => 'No published commercial hours — verify locally.',
                'season_info' => 'Stillwater coarse fishing year-round subject to access rules.',
                'tactics_guide' => 'Simple pleasure tactics — maggot, worm, corn and pellet for silvers and smaller carp; keep an eye on margins for better fish.',
                'is_complex' => false,
                'is_approved' => true,
                'manager_verified' => false,
            ],
        );

        $water = Water::query()->updateOrCreate(
            [
                'venue_id' => $venue->id,
                'name' => 'Burradon Pond',
            ],
            [
                'description' => 'Colliery-reclamation pond — mixed coarse fishing in an urban-fringe setting.',
                'sort_order' => 1,
            ],
        );

        $speciesIds = Species::query()
            ->whereIn('slug', ['carp', 'roach', 'rudd', 'perch', 'bream', 'tench'])
            ->pluck('id')
            ->all();

        if ($speciesIds !== []) {
            $water->species()->sync($speciesIds);
        }
    }

    public function down(): void
    {
        $venue = Venue::query()->where('slug', 'burradon-pond')->first();

        if (! $venue) {
            return;
        }

        $venue->waters()->each(function (Water $water): void {
            $water->species()->detach();
            $water->delete();
        });

        $venue->delete();
    }
};
