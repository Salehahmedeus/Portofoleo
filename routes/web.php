<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', HomeController::class)->name('home');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

Route::post('/contact-submissions', [ContactController::class, 'store'])
    ->middleware('throttle:contact-submissions')
    ->name('contact-submissions.store');

Route::post('/analytics-events', [AnalyticsEventController::class, 'store'])
    ->middleware('throttle:analytics-events')
    ->name('analytics-events.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';

Route::fallback(function (Request $request): Response {
    if ($request->is('api/*')) {
        abort(404);
    }

    return Inertia::render('errors/not-found')
        ->toResponse($request)
        ->setStatusCode(404);
});
