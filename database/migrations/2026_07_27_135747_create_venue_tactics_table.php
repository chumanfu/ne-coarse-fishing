<?php

use App\Models\FishingSession;
use App\Models\VenueTactic;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_tactics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fishing_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('water_id')->nullable()->constrained()->nullOnDelete();
            $table->string('peg_number')->nullable();
            $table->text('body');
            $table->date('fished_at')->nullable();
            $table->timestamps();

            $table->unique('fishing_session_id');
            $table->index(['venue_id', 'created_at']);
        });

        FishingSession::query()
            ->whereNotNull('tactics_tip')
            ->where('tactics_tip', '!=', '')
            ->orderBy('id')
            ->each(function (FishingSession $session): void {
                VenueTactic::query()->create([
                    'venue_id' => $session->venue_id,
                    'user_id' => $session->user_id,
                    'fishing_session_id' => $session->id,
                    'water_id' => $session->water_id,
                    'peg_number' => $session->peg_number,
                    'body' => trim($session->tactics_tip),
                    'fished_at' => $session->fished_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_tactics');
    }
};
