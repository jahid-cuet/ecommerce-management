<?php

use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\ListingController;
use App\Http\Controllers\Owner\ProductController;
use App\Http\Controllers\Owner\ShopController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:shop_owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('shops', [ShopController::class, 'index'])->name('shops.index');

        // Everything under a shop is limited to that shop's owner, and a listing must belong to the shop in the URL.
        Route::middleware('can:manage,shop')->scopeBindings()->group(function () {
            Route::get('shops/{shop}/catalog', [ListingController::class, 'catalog'])->name('shops.catalog');
            Route::resource('shops.listings', ListingController::class)->except('show');
        });

        // Owners add products to the shared catalog and edit the ones they created.
        Route::resource('products', ProductController::class)->only(['index', 'create', 'store']);
        Route::middleware('can:update,product')->group(function () {
            Route::resource('products', ProductController::class)->only(['edit', 'update']);
        });
    });
