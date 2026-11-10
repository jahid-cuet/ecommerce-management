<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $shops = $user->shops()
            ->withCount('listings')
            ->orderBy('name')
            ->get()
            ->map(fn (Shop $shop) => [
                ...$shop->only(['id', 'name', 'slug', 'is_active']),
                'listings_count' => $shop->listings_count,
            ]);

        return Inertia::render('owner/dashboard', [
            'stats' => [
                'shops' => $shops->count(),
                'listings' => $shops->sum('listings_count'),
                'ownProducts' => Product::where('created_by', $user->id)->count(),
                'catalogProducts' => Product::count(),
            ],
            'shops' => $shops,
        ]);
    }
}
