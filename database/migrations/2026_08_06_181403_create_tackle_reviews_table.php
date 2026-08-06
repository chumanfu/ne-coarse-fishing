<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tackle_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('brand')->nullable();
            $table->unsignedTinyInteger('rating')->default(0); // 0–5
            $table->text('body');
            $table->string('purchase_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('featured_on_home')->default(false);
            $table->timestamps();

            $table->index(['is_published', 'featured_on_home']);
        });

        Schema::create('tackle_review_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tackle_review_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tackle_review_photos');
        Schema::dropIfExists('tackle_reviews');
    }
};
