<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->after('is_published')->constrained('users')->nullOnDelete();
            $table->boolean('manager_verified')->default(false)->after('manager_id');
        });

        Schema::table('tackle_shops', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->after('is_published')->constrained('users')->nullOnDelete();
            $table->boolean('manager_verified')->default(false)->after('manager_id');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn('manager_verified');
        });

        Schema::table('tackle_shops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn('manager_verified');
        });
    }
};
