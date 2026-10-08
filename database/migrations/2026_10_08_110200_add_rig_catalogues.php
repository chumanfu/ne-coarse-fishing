<?php

use App\Support\RwRigs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peg_float_rigs', function (Blueprint $table) {
            $table->string('catalogue', 32)->nullable()->after('is_system');
            $table->boolean('use_stored_grams')->default(false)->after('catalogue');
            $table->index('catalogue');
        });

        DB::table('peg_float_rigs')->where('is_system', true)->update(['catalogue' => 'standard']);

        RwRigs::install();
    }

    public function down(): void
    {
        DB::table('peg_float_rigs')->where('catalogue', 'rw')->delete();

        Schema::table('peg_float_rigs', function (Blueprint $table) {
            $table->dropIndex(['catalogue']);
            $table->dropColumn(['catalogue', 'use_stored_grams']);
        });
    }
};
