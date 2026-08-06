<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('url');
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('facebook_url');
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('facebook_url');
        });
    }
};
