<?php

use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.health');

Route::get('/home', HomeController::class)->name('api.home');
Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('api.projects.show');

Route::post('/analytics-events', [AnalyticsEventController::class, 'trackEvent'])
    ->middleware('throttle:analytics-events')
    ->name('api.analytics-events.track');
