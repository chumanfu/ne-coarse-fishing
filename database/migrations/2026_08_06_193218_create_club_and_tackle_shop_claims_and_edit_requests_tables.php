<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['club_id', 'status']);
        });

        Schema::create('tackle_shop_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tackle_shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['tackle_shop_id', 'status']);
        });

        Schema::create('club_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->json('proposed_data');
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status']);
        });

        Schema::create('tackle_shop_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tackle_shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->json('proposed_data');
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['tackle_shop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tackle_shop_edit_requests');
        Schema::dropIfExists('club_edit_requests');
        Schema::dropIfExists('tackle_shop_claims');
        Schema::dropIfExists('club_claims');
    }
};
