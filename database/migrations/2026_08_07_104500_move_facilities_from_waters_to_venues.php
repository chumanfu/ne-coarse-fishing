<?php

use App\Models\Venue;
use App\Models\Water;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->json('facilities')->nullable()->after('tactics_guide');
        });

        if (Schema::hasColumn('waters', 'facilities')) {
            Water::query()
                ->whereNotNull('facilities')
                ->with('venue')
                ->get()
                ->groupBy('venue_id')
                ->each(function ($waters) {
                    $venue = $waters->first()->venue;

                    if (! $venue) {
                        return;
                    }

                    $merged = Venue::normalizeFacilities(
                        $waters->flatMap(fn (Water $water) => $water->facilities ?? [])->all()
                    );

                    $venue->update(['facilities' => $merged !== [] ? $merged : null]);
                });

            Schema::table('waters', function (Blueprint $table) {
                $table->dropColumn('facilities');
            });
        }
    }

    public function down(): void
    {
        Schema::table('waters', function (Blueprint $table) {
            $table->json('facilities')->nullable()->after('depth_info');
        });

        Venue::query()
            ->whereNotNull('facilities')
            ->with('waters')
            ->get()
            ->each(function (Venue $venue) {
                $water = $venue->waters->first();

                if ($water) {
                    $water->update(['facilities' => $venue->facilities]);
                }
            });

        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('facilities');
        });
    }
};
