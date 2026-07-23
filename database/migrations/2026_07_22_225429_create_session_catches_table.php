<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_catches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishing_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight_lb', 6, 2)->nullable();
            $table->string('bait')->nullable();
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_catches');
    }
};
