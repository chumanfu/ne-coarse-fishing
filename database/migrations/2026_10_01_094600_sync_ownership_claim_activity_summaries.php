<?php

use App\Models\Activity;
use App\Models\ClubClaim;
use App\Models\TackleShopClaim;
use App\Models\VenueClaim;
use App\Services\ActivityLogger;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activities')) {
            return;
        }

        $logger = app(ActivityLogger::class);

        VenueClaim::query()->whereIn('status', ['approved', 'rejected'])->each(
            fn (VenueClaim $claim) => $logger->ownershipClaimStatusChanged($claim)
        );
        ClubClaim::query()->whereIn('status', ['approved', 'rejected'])->each(
            fn (ClubClaim $claim) => $logger->ownershipClaimStatusChanged($claim)
        );
        TackleShopClaim::query()->whereIn('status', ['approved', 'rejected'])->each(
            fn (TackleShopClaim $claim) => $logger->ownershipClaimStatusChanged($claim)
        );
    }

    public function down(): void
    {
        Activity::query()
            ->whereIn('type', [
                Activity::TYPE_VENUE_CLAIM,
                Activity::TYPE_CLUB_CLAIM,
                Activity::TYPE_SHOP_CLAIM,
            ])
            ->whereIn('summary', ['Approved', 'Rejected'])
            ->update(['summary' => 'Pending review']);
    }
};
