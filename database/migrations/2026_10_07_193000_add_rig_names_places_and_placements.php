<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peg_float_rigs', function (Blueprint $table) {
            $table->string('name')->nullable()->after('user_id');
            $table->json('placements')->nullable()->after('notes');
        });

        Schema::create('peg_float_rig_venue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peg_float_rig_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['peg_float_rig_id', 'venue_id']);
        });

        Schema::create('peg_float_rig_peg', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peg_float_rig_id')->constrained()->cascadeOnDelete();
            $table->foreignId('water_peg_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['peg_float_rig_id', 'water_peg_id']);
        });

        DB::table('peg_float_rigs')->whereNull('name')->update(['name' => DB::raw('float_name')]);

        $now = now();
        foreach (DB::table('peg_float_rigs')->whereNotNull('water_peg_id')->get(['id', 'water_peg_id']) as $rig) {
            DB::table('peg_float_rig_peg')->insert([
                'peg_float_rig_id' => $rig->id,
                'water_peg_id' => $rig->water_peg_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $venueId = DB::table('water_pegs')
                ->join('waters', 'waters.id', '=', 'water_pegs.water_id')
                ->where('water_pegs.id', $rig->water_peg_id)
                ->value('waters.venue_id');

            if ($venueId) {
                DB::table('peg_float_rig_venue')->insert([
                    'peg_float_rig_id' => $rig->id,
                    'venue_id' => $venueId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('peg_float_rigs', function (Blueprint $table) {
            $table->unsignedBigInteger('water_peg_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peg_float_rig_peg');
        Schema::dropIfExists('peg_float_rig_venue');

        Schema::table('peg_float_rigs', function (Blueprint $table) {
            $table->dropColumn(['name', 'placements']);
        });
    }
};
