<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\YouTubeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/content', [ContentController::class, 'index'])->name('content.index');
    Route::get('/content/{contentItem}', [ContentController::class, 'show'])->name('content.show');
    Route::post('/content/import-url', [YouTubeController::class, 'importUrl'])->name('content.import-url');

    Route::get('/youtube/search', [YouTubeController::class, 'search'])->name('youtube.search');
    Route::post('/youtube/import', [YouTubeController::class, 'import'])->name('youtube.import');

    // Content review workflow
    Route::get('/content/{contentItem}/review', [ReviewController::class, 'review'])->name('content.review');
    Route::post('/content/{contentItem}/approve', [ReviewController::class, 'approve'])->name('content.approve');
    Route::post('/content/{contentItem}/reject', [ReviewController::class, 'reject'])->name('content.reject');
    Route::post('/content/{contentItem}/publish', [ReviewController::class, 'publish'])->name('content.publish');
    Route::post('/content/{contentItem}/disable', [ReviewController::class, 'disable'])->name('content.disable');
    Route::post('/content/{contentItem}/re-review', [ReviewController::class, 'reReview'])->name('content.re-review');
    Route::put('/content/{contentItem}/classification', [ReviewController::class, 'updateClassification'])->name('content.classification');
});
