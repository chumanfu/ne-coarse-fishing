<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('source')->default('contact'); // contact | admin
            $table->string('status')->default('open'); // open | closed
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('admin_last_read_at')->nullable();
            $table->timestamp('participant_last_read_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_message_at']);
            $table->index('contact_email');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('is_from_admin')->default(false);
            $table->timestamps();

            $table->index(['message_thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
    }
};
