<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waters', function (Blueprint $table) {
            $table->json('geometry')->nullable()->after('map_image_path');
            $table->string('geometry_type')->nullable()->after('geometry');
        });
    }

    public function down(): void
    {
        Schema::table('waters', function (Blueprint $table) {
            $table->dropColumn(['geometry', 'geometry_type']);
        });
    }
};
