<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('overview')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            $table->text('directions')->nullable();
            $table->text('day_ticket_info')->nullable();
            $table->text('membership_info')->nullable();
            $table->string('ticket_type')->default('day_ticket'); // day_ticket, club, syndicate, mixed
            $table->text('opening_times')->nullable();
            $table->text('season_info')->nullable();
            $table->text('tactics_guide')->nullable();
            $table->boolean('is_complex')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('manager_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
