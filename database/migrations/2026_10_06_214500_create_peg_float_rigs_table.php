<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peg_float_rigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_peg_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('float_name');
            $table->string('float_size', 50);
            $table->string('float_type', 20);
            $table->decimal('float_grams', 6, 3);
            $table->decimal('depth', 5, 2);
            $table->string('depth_unit', 2);
            $table->string('pattern_id', 30);
            $table->decimal('olivette_grams', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['water_peg_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peg_float_rigs');
    }
};
