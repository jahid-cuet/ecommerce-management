<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Store\StorefrontController;
use Illuminate\Support\Facades\Route;

// Public storefront for customers.
Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('shops/{shop:slug}', [StorefrontController::class, 'shop'])->name('store.shops.show');
// The controller checks that the shop sells the product, so Laravel's automatic child scoping is not used here.
Route::get('shops/{shop:slug}/products/{product:slug}', [StorefrontController::class, 'product'])
    ->withoutScopedBindings()
    ->name('store.products.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/admin.php';
require __DIR__.'/owner.php';
require __DIR__.'/settings.php';
