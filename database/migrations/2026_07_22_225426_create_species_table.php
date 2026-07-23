<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('water_species', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_id')->constrained()->cascadeOnDelete();
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table->unique(['water_id', 'species_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_species');
        Schema::dropIfExists('species');
    }
};
