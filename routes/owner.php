<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:shop_owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::inertia('dashboard', 'owner/dashboard')->name('dashboard');
    });
