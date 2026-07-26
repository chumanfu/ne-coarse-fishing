<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\FishingSessionController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MatchReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VenueClaimController;
use App\Http\Controllers\VenueController;
use App\Models\Venue;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $featured = Venue::query()
        ->approved()
        ->with(['waters.species', 'manager'])
        ->latest()
        ->take(6)
        ->get();

    return view('home', [
        'featured' => $featured,
        'venueCount' => Venue::approved()->count(),
    ]);
})->name('home');

Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
Route::get('/venues/{venue:slug}', [VenueController::class, 'show'])->name('venues.show');
Route::get('/map', [MapController::class, 'index'])->name('map.index');

Route::get('/dashboard', function () {
    $user = auth()->user();

    return view('dashboard', [
        'mySessions' => $user->fishingSessions()->with('venue')->latest('fished_at')->take(5)->get(),
        'myVenues' => $user->venues()->latest()->take(5)->get(),
        'managedVenues' => $user->managedVenues()->latest()->take(5)->get(),
    ]);
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/venues/create/new', [VenueController::class, 'create'])->name('venues.create');
    Route::post('/venues', [VenueController::class, 'store'])->name('venues.store');
    Route::get('/venues/{venue:slug}/edit', [VenueController::class, 'edit'])->name('venues.edit');
    Route::put('/venues/{venue:slug}', [VenueController::class, 'update'])->name('venues.update');
    Route::delete('/venues/{venue:slug}', [VenueController::class, 'destroy'])->name('venues.destroy');

    Route::post('/venues/{venue:slug}/claim', [VenueClaimController::class, 'store'])->name('venues.claim');

    Route::get('/venues/{venue:slug}/match-reports/create', [MatchReportController::class, 'create'])->name('match-reports.create');
    Route::post('/venues/{venue:slug}/match-reports', [MatchReportController::class, 'store'])->name('match-reports.store');
    Route::delete('/match-reports/{matchReport}', [MatchReportController::class, 'destroy'])->name('match-reports.destroy');

    Route::get('/venues/{venue:slug}/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/venues/{venue:slug}/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('/sessions', [FishingSessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/create', [FishingSessionController::class, 'create'])->name('sessions.create');
    Route::post('/sessions', [FishingSessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/{fishingSession}', [FishingSessionController::class, 'show'])->name('sessions.show');
    Route::delete('/sessions/{fishingSession}', [FishingSessionController::class, 'destroy'])->name('sessions.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
