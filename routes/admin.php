<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SkillController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::redirect('/', '/dashboard')->name('dashboard');

        Route::patch('projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
        Route::patch('projects/{project}/featured', [ProjectController::class, 'toggleFeatured'])->name('projects.toggle-featured');
        Route::delete('projects/{project}/images/{image}', [ProjectController::class, 'destroyImage'])->name('projects.images.destroy');
        Route::resource('projects', ProjectController::class)->except('show');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

        Route::get('content', [ContentController::class, 'edit'])->name('content.edit');
        Route::put('content', [ContentController::class, 'update'])->name('content.update');

        Route::patch('services/reorder', [ServiceController::class, 'reorder'])->name('services.reorder');
        Route::resource('services', ServiceController::class)->except(['create', 'edit']);

        Route::patch('skills/reorder', [SkillController::class, 'reorder'])->name('skills.reorder');
        Route::resource('skills', SkillController::class)->except(['create', 'edit']);

        Route::resource('settings', SettingsController::class)->except(['create', 'edit']);

        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
