<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Feed\LikeSaveController;
use App\Http\Controllers\Feed\PublicFeedController;
use App\Http\Controllers\OnboardingController;
use App\Http\Middleware\EnsureOnboardingComplete;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicFeedController::class, 'index'])->name('feed');
Route::get('/feed', [PublicFeedController::class, 'index'])->name('feed.alias');

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::post('/feed/{contentItem}/like', [LikeSaveController::class, 'toggleLike'])->name('feed.like');
    Route::post('/feed/{contentItem}/save', [LikeSaveController::class, 'toggleSave'])->name('feed.save');
});

Route::middleware(['auth', 'verified', EnsureOnboardingComplete::class])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
