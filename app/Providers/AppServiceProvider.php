<?php

namespace App\Providers;

use App\Models\Club;
use App\Models\TackleShop;
use App\Models\Venue;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Venue::saved(function (Venue $venue): void {
            if (! Schema::hasTable('activities') || ! $venue->is_approved) {
                return;
            }

            if ($venue->wasRecentlyCreated || $venue->wasChanged('is_approved')) {
                app(ActivityLogger::class)->venueAdded($venue);
            }
        });

        Club::created(function (Club $club): void {
            if (! Schema::hasTable('activities')) {
                return;
            }

            app(ActivityLogger::class)->clubAdded($club);
        });

        TackleShop::created(function (TackleShop $shop): void {
            if (! Schema::hasTable('activities')) {
                return;
            }

            app(ActivityLogger::class)->tackleShopAdded($shop);
        });
    }
}
