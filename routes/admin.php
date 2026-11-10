<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\ShopOwnerController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// {shop_owner} only resolves to shop owner accounts, never to admins.
Route::bind('shop_owner', fn (string $id) => User::query()
    ->where('role', UserRole::ShopOwner)
    ->findOrFail($id));

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::resource('shop-owners', ShopOwnerController::class)->except('show');
        Route::resource('shops', ShopController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');
    });
