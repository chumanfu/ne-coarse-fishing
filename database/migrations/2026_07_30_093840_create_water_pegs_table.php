<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_pegs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('number')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('fishing_sessions', function (Blueprint $table) {
            $table->foreignId('water_peg_id')->nullable()->after('water_id')->constrained('water_pegs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fishing_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('water_peg_id');
        });

        Schema::dropIfExists('water_pegs');
    }
};
