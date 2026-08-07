<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waters', function (Blueprint $table) {
            $table->json('facilities')->nullable()->after('depth_info');
        });
    }

    public function down(): void
    {
        Schema::table('waters', function (Blueprint $table) {
            $table->dropColumn('facilities');
        });
    }
};
