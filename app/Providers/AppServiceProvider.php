<?php

namespace App\Providers;

use App\Models\Club;
use App\Models\SiteAnnouncement;
use App\Models\TackleShop;
use App\Models\Venue;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            if ($user instanceof User && $user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });

        View::composer('layouts.app', function ($view): void {
            if (! Schema::hasTable('site_announcements')) {
                $view->with('siteAnnouncements', collect());

                return;
            }

            $view->with('siteAnnouncements', SiteAnnouncement::query()->currentlyVisible()->get());
        });

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
