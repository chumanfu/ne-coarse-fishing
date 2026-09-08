<?php

use App\Support\Weight;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_catches', function (Blueprint $table) {
            // Grams, so lb/oz and kg both round-trip without decimal drift.
            $table->unsignedInteger('weight_g')->nullable()->after('species_id');
            // 'individual' = one fish; 'bag' = a species total for the session.
            $table->string('entry_type', 20)->default('individual')->after('weight_g');
            // Singles out a specimen recorded alongside a bag total.
            $table->boolean('is_notable')->default(false)->after('quantity');
            // Echoes the entry back in whichever unit the angler typed.
            $table->string('entered_unit', 10)->default(Weight::UNIT_LB_OZ)->after('is_notable');
        });

        DB::table('session_catches')
            ->whereNotNull('weight_lb')
            ->orderBy('id')
            ->each(function (object $catch) {
                DB::table('session_catches')
                    ->where('id', $catch->id)
                    ->update(['weight_g' => Weight::fromDecimalPounds($catch->weight_lb)?->grams]);
            });

        // Rows that recorded several fish were always a bag total in practice.
        DB::table('session_catches')->where('quantity', '>', 1)->update(['entry_type' => 'bag']);

        Schema::table('session_catches', function (Blueprint $table) {
            $table->dropColumn('weight_lb');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_weight_unit', 10)
                ->default(Weight::UNIT_LB_OZ)
                ->after('google_id');
        });

        // Original column was TINYINT (max 255). Silver-fish bags routinely go past that.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE session_catches MODIFY quantity INT UNSIGNED NOT NULL DEFAULT 1');
        }
    }

    public function down(): void
    {
        Schema::table('session_catches', function (Blueprint $table) {
            $table->decimal('weight_lb', 6, 2)->nullable()->after('species_id');
        });

        DB::table('session_catches')
            ->whereNotNull('weight_g')
            ->orderBy('id')
            ->each(function (object $catch) {
                DB::table('session_catches')
                    ->where('id', $catch->id)
                    ->update(['weight_lb' => round($catch->weight_g / 453.59237, 2)]);
            });

        Schema::table('session_catches', function (Blueprint $table) {
            $table->dropColumn(['weight_g', 'entry_type', 'is_notable', 'entered_unit']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_weight_unit');
        });
    }
};
