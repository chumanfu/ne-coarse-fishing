<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fishing_sessions', function (Blueprint $table) {
            $table->decimal('peg_latitude', 10, 7)->nullable()->after('peg_number');
            $table->decimal('peg_longitude', 10, 7)->nullable()->after('peg_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('fishing_sessions', function (Blueprint $table) {
            $table->dropColumn(['peg_latitude', 'peg_longitude']);
        });
    }
};
