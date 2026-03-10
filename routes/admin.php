<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::redirect('/', '/dashboard')->name('dashboard');

        Route::resource('projects', ProjectController::class)->except('show');
        Route::patch('projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
        Route::patch('projects/{project}/featured', [ProjectController::class, 'toggleFeatured'])->name('projects.toggle-featured');
        Route::delete('projects/{project}/images/{image}', [ProjectController::class, 'destroyImage'])->name('projects.images.destroy');

        Route::get('content', [ContentController::class, 'edit'])->name('content.edit');
        Route::put('content', [ContentController::class, 'update'])->name('content.update');

        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
