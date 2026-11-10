<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/dashboard', [
            'stats' => [
                'shopOwners' => User::where('role', UserRole::ShopOwner)->count(),
                'shops' => Shop::count(),
                'activeShops' => Shop::where('is_active', true)->count(),
            ],
        ]);
    }
}
