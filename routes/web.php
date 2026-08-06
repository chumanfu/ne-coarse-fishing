<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FishingSessionController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MatchReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TackleShopController;
use App\Http\Controllers\VenueClaimController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\VenueEditRequestController;
use App\Http\Controllers\VenueFavouriteController;
use App\Http\Controllers\VenueTacticController;
use App\Models\Activity;
use App\Models\Club;
use App\Models\TackleShop;
use App\Models\Venue;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $featured = Venue::query()
        ->approved()
        ->with(['waters.species', 'manager', 'photos'])
        ->latest()
        ->take(6)
        ->get();

    $featuredShops = TackleShop::query()
        ->published()
        ->featured()
        ->ordered()
        ->take(6)
        ->get();

    $featuredClubs = Club::query()
        ->published()
        ->featured()
        ->ordered()
        ->take(6)
        ->get();

    $activities = Activity::query()
        ->with('user')
        ->latest()
        ->take(5)
        ->get();

    $mapVenues = Venue::query()
        ->approved()
        ->with(['waters.species'])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->orderBy('name')
        ->get();

    $venueMarkers = $mapVenues->map(fn (Venue $venue) => [
        'id' => 'venue-'.$venue->id,
        'type' => 'venue',
        'name' => $venue->name,
        'lat' => $venue->latitude,
        'lng' => $venue->longitude,
        'ticket_type' => $venue->ticketTypeLabel(),
        'address' => $venue->address,
        'verified' => $venue->manager_verified,
        'url' => route('venues.show', $venue),
        'overview' => str($venue->overview)->limit(120)->toString(),
        'species' => $venue->allSpecies()->take(4)->pluck('name')->values(),
    ]);

    $shopMarkers = TackleShop::query()
        ->published()
        ->mappable()
        ->ordered()
        ->get()
        ->map(fn (TackleShop $shop) => [
            'id' => 'shop-'.$shop->id,
            'type' => 'tackle_shop',
            'name' => $shop->name,
            'lat' => $shop->latitude,
            'lng' => $shop->longitude,
            'ticket_type' => $shop->locationTypeLabel(),
            'address' => $shop->address ?: $shop->town,
            'verified' => false,
            'url' => route('tackle-shops.show', $shop),
            'overview' => str($shop->overview)->limit(120)->toString(),
            'species' => [],
        ]);

    $mapMarkers = $venueMarkers->concat($shopMarkers)->values();

    return view('home', [
        'featured' => $featured,
        'featuredShops' => $featuredShops,
        'featuredClubs' => $featuredClubs,
        'activities' => $activities,
        'mapMarkers' => $mapMarkers,
        'venueCount' => Venue::approved()->count(),
    ]);
})->name('home');

Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
Route::get('/venues/{venue:slug}', [VenueController::class, 'show'])->name('venues.show');
Route::get('/tackle-shops', [TackleShopController::class, 'index'])->name('tackle-shops.index');
Route::get('/tackle-shops/{tackleShop:slug}', [TackleShopController::class, 'show'])->name('tackle-shops.show');
Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
Route::get('/clubs/{club:slug}', [ClubController::class, 'show'])->name('clubs.show');
Route::get('/map', [MapController::class, 'index'])->name('map.index');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/dashboard', function () {
    $user = auth()->user();

    return view('dashboard', [
        'mySessions' => $user->fishingSessions()->with('venue')->latest('fished_at')->take(5)->get(),
        'myVenues' => $user->venues()->latest()->take(5)->get(),
        'managedVenues' => $user->managedVenues()->latest()->take(5)->get(),
        'favouriteVenues' => $user->favouriteVenues()->approved()->orderBy('name')->take(5)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/venues/create/new', [VenueController::class, 'create'])->name('venues.create');
    Route::get('/venues/{venue:slug}/edit', [VenueController::class, 'edit'])->name('venues.edit');
    Route::get('/venues/{venue:slug}/suggest-edit', [VenueEditRequestController::class, 'create'])->name('venues.suggest-edit');
    Route::delete('/venues/{venue:slug}', [VenueController::class, 'destroy'])->name('venues.destroy');

    Route::post('/venues/{venue:slug}/claim', [VenueClaimController::class, 'store'])->name('venues.claim');
    Route::post('/venues/{venue:slug}/favourite', [VenueFavouriteController::class, 'store'])->name('venues.favourite.store');
    Route::delete('/venues/{venue:slug}/favourite', [VenueFavouriteController::class, 'destroy'])->name('venues.favourite.destroy');
    Route::get('/favourites', [VenueFavouriteController::class, 'index'])->name('venues.favourites');
    Route::get('/venues/{venue:slug}/pegs/create', [\App\Http\Controllers\WaterPegController::class, 'create'])->name('pegs.create');
    Route::post('/venues/{venue:slug}/pegs', [\App\Http\Controllers\WaterPegController::class, 'store'])->name('pegs.store');
    Route::post('/venues/{venue:slug}/pegs/{waterPeg}/verify', [\App\Http\Controllers\WaterPegController::class, 'verify'])->name('pegs.verify');
    Route::delete('/venues/{venue:slug}/pegs/{waterPeg}', [\App\Http\Controllers\WaterPegController::class, 'destroy'])->name('pegs.destroy');

    Route::get('/venues/{venue:slug}/match-reports/create', [MatchReportController::class, 'create'])->name('match-reports.create');
    Route::post('/venues/{venue:slug}/match-reports', [MatchReportController::class, 'store'])->name('match-reports.store');
    Route::delete('/match-reports/{matchReport}', [MatchReportController::class, 'destroy'])->name('match-reports.destroy');

    Route::get('/venues/{venue:slug}/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/venues/{venue:slug}/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('/sessions', [FishingSessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/create', [FishingSessionController::class, 'create'])->name('sessions.create');
    Route::post('/sessions', [FishingSessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/{fishingSession}/edit', [FishingSessionController::class, 'edit'])->name('sessions.edit');
    Route::patch('/sessions/{fishingSession}', [FishingSessionController::class, 'update'])->name('sessions.update');
    Route::delete('/sessions/{fishingSession}', [FishingSessionController::class, 'destroy'])->name('sessions.destroy');

    Route::get('/venues/{venue:slug}/tactics/create', [VenueTacticController::class, 'create'])->name('tactics.create');
    Route::post('/venues/{venue:slug}/tactics', [VenueTacticController::class, 'store'])->name('tactics.store');
    Route::get('/tactics/{venueTactic}/edit', [VenueTacticController::class, 'edit'])->name('tactics.edit');
    Route::patch('/tactics/{venueTactic}', [VenueTacticController::class, 'update'])->name('tactics.update');
    Route::delete('/tactics/{venueTactic}', [VenueTacticController::class, 'destroy'])->name('tactics.destroy');
});

Route::get('/sessions/{fishingSession}', [FishingSessionController::class, 'show'])->name('sessions.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
