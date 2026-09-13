<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_catch_species', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_catch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table->unique(['session_catch_id', 'species_id']);
        });

        $catches = DB::table('session_catches')
            ->select('id', 'species_id')
            ->whereNotNull('species_id')
            ->orderBy('id')
            ->get();

        foreach ($catches as $catch) {
            DB::table('session_catch_species')->insert([
                'session_catch_id' => $catch->id,
                'species_id' => $catch->species_id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('session_catch_species');
    }
};
