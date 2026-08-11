<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('address');
            $table->timestamp('invite_sent_at')->nullable()->after('contact_email');
        });

        Schema::table('tackle_shops', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('phone');
            $table->timestamp('invite_sent_at')->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'invite_sent_at']);
        });

        Schema::table('tackle_shops', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'invite_sent_at']);
        });
    }
};
