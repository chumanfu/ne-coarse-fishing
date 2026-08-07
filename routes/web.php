<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ClubClaimController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ClubEditRequestController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FishingSessionController;
use App\Http\Controllers\GdprExportController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MatchReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferFriendController;
use App\Http\Controllers\TackleReviewController;
use App\Http\Controllers\TackleShopClaimController;
use App\Http\Controllers\TackleShopController;
use App\Http\Controllers\TackleShopEditRequestController;
use App\Http\Controllers\VenueClaimController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\VenueEditRequestController;
use App\Http\Controllers\VenueFavouriteController;
use App\Http\Controllers\VenueTacticController;
use App\Models\Activity;
use App\Models\Club;
use App\Models\MessageThread;
use App\Models\TackleReview;
use App\Models\TackleShop;
use App\Models\Venue;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $featured = Venue::query()
        ->approved()
        ->with(['waters.species', 'manager', 'photos'])
        ->latest()
        ->take(4)
        ->get();

    $featuredShops = TackleShop::query()
        ->published()
        ->featured()
        ->ordered()
        ->take(4)
        ->get();

    $featuredClubs = Club::query()
        ->published()
        ->featured()
        ->ordered()
        ->take(4)
        ->get();

    $activities = Activity::query()
        ->with('user')
        ->latest()
        ->take(10)
        ->get();

    $tackleReviews = TackleReview::query()
        ->featured()
        ->with(['user', 'photos'])
        ->latest()
        ->take(3)
        ->get();

    if ($tackleReviews->isEmpty()) {
        $tackleReviews = TackleReview::query()
            ->published()
            ->with(['user', 'photos'])
            ->latest()
            ->take(3)
            ->get();
    }

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
        'tackleReviews' => $tackleReviews,
        'mapMarkers' => $mapMarkers,
        'venueCount' => Venue::approved()->count(),
    ]);
})->name('home');

Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
Route::get('/about', AboutController::class)->name('about');
Route::get('/refer', ReferFriendController::class)->name('refer');
Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
Route::get('/venues/{venue:slug}', [VenueController::class, 'show'])->name('venues.show');
Route::get('/tackle-shops', [TackleShopController::class, 'index'])->name('tackle-shops.index');
Route::get('/tackle-shops/{tackleShop:slug}', [TackleShopController::class, 'show'])->name('tackle-shops.show');
Route::get('/tackle-reviews', [TackleReviewController::class, 'index'])->name('tackle-reviews.index');
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
        'myMessages' => MessageThread::query()
            ->forParticipant($user)
            ->latest('last_message_at')
            ->take(5)
            ->get(),
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

    Route::get('/clubs/{club:slug}/edit', [ClubController::class, 'edit'])->name('clubs.edit');
    Route::patch('/clubs/{club:slug}', [ClubController::class, 'update'])->name('clubs.update');
    Route::get('/clubs/{club:slug}/suggest-edit', [ClubEditRequestController::class, 'create'])->name('clubs.suggest-edit');
    Route::post('/clubs/{club:slug}/suggest-edit', [ClubEditRequestController::class, 'store'])->name('clubs.suggest-edit.store');
    Route::post('/clubs/{club:slug}/claim', [ClubClaimController::class, 'store'])->name('clubs.claim');

    Route::get('/tackle-shops/{tackleShop:slug}/edit', [TackleShopController::class, 'edit'])->name('tackle-shops.edit');
    Route::patch('/tackle-shops/{tackleShop:slug}', [TackleShopController::class, 'update'])->name('tackle-shops.update');
    Route::get('/tackle-shops/{tackleShop:slug}/suggest-edit', [TackleShopEditRequestController::class, 'create'])->name('tackle-shops.suggest-edit');
    Route::post('/tackle-shops/{tackleShop:slug}/suggest-edit', [TackleShopEditRequestController::class, 'store'])->name('tackle-shops.suggest-edit.store');
    Route::post('/tackle-shops/{tackleShop:slug}/claim', [TackleShopClaimController::class, 'store'])->name('tackle-shops.claim');
    Route::get('/venues/{venue:slug}/pegs/create', [\App\Http\Controllers\WaterPegController::class, 'create'])->name('pegs.create');
    Route::post('/venues/{venue:slug}/pegs', [\App\Http\Controllers\WaterPegController::class, 'store'])->name('pegs.store');
    Route::get('/venues/{venue:slug}/pegs/{waterPeg}/edit', [\App\Http\Controllers\WaterPegController::class, 'edit'])->name('pegs.edit');
    Route::put('/venues/{venue:slug}/pegs/{waterPeg}', [\App\Http\Controllers\WaterPegController::class, 'update'])->name('pegs.update');
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

    Route::get('/tackle-reviews/create', [TackleReviewController::class, 'create'])->name('tackle-reviews.create');
    Route::post('/tackle-reviews', [TackleReviewController::class, 'store'])->name('tackle-reviews.store');
    Route::get('/tackle-reviews/{tackleReview}/edit', [TackleReviewController::class, 'edit'])->name('tackle-reviews.edit');
    Route::patch('/tackle-reviews/{tackleReview}', [TackleReviewController::class, 'update'])->name('tackle-reviews.update');
    Route::delete('/tackle-reviews/{tackleReview}', [TackleReviewController::class, 'destroy'])->name('tackle-reviews.destroy');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{messageThread}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{messageThread}/reply', [MessageController::class, 'reply'])->name('messages.reply');
});

Route::get('/tackle-reviews/{tackleReview}', [TackleReviewController::class, 'show'])
    ->whereNumber('tackleReview')
    ->name('tackle-reviews.show');

Route::get('/sessions/{fishingSession}', [FishingSessionController::class, 'show'])->name('sessions.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/data-export', GdprExportController::class)
        ->middleware('throttle:3,60')
        ->name('profile.data-export');
});

require __DIR__.'/auth.php';
