<?php

use App\Support\SystemRigs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peg_float_rigs', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('user_id');
            $table->string('system_key', 40)->nullable()->unique()->after('is_system');
        });

        Schema::table('peg_float_rigs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        SystemRigs::install();
    }

    public function down(): void
    {
        DB::table('peg_float_rigs')->where('is_system', true)->delete();

        Schema::table('peg_float_rigs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn(['is_system', 'system_key']);
        });
    }
};
