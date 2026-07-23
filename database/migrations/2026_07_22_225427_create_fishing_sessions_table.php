<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fishing_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('water_id')->nullable()->constrained()->nullOnDelete();
            $table->date('fished_at');
            $table->unsignedSmallInteger('duration_hours')->nullable();
            $table->string('weather')->nullable();
            $table->string('peg_number')->nullable();
            $table->text('commentary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fishing_sessions');
    }
};
