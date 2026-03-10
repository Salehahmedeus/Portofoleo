<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::redirect('/', '/dashboard')->name('dashboard');
    });
