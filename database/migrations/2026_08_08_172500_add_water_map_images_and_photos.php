<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waters', function (Blueprint $table) {
            $table->string('map_image_path')->nullable()->after('depth_info');
        });

        Schema::table('water_pegs', function (Blueprint $table) {
            $table->decimal('map_x', 8, 4)->nullable()->after('longitude');
            $table->decimal('map_y', 8, 4)->nullable()->after('map_x');
        });

        // Fresh installs already have nullable lat/lng; tighten only on MySQL/Postgres
        // where older rows may still be non-nullable.
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            Schema::table('water_pegs', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->change();
                $table->decimal('longitude', 10, 7)->nullable()->change();
            });
        }

        DB::table('water_pegs')->update([
            'latitude' => null,
            'longitude' => null,
            'map_x' => null,
            'map_y' => null,
        ]);

        Schema::create('water_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['water_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_photos');

        Schema::table('water_pegs', function (Blueprint $table) {
            $table->dropColumn(['map_x', 'map_y']);
        });

        // Restore non-null coords with a placeholder so the column can be tightened again.
        DB::table('water_pegs')->whereNull('latitude')->update([
            'latitude' => 54.9780,
            'longitude' => -1.6178,
        ]);

        Schema::table('water_pegs', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();
        });

        Schema::table('waters', function (Blueprint $table) {
            $table->dropColumn('map_image_path');
        });
    }
};
