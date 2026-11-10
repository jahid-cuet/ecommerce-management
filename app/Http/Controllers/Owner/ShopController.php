<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    /**
     * The signed-in owner's shops. Shops themselves are created by admins.
     */
    public function index(Request $request): Response
    {
        $shops = $request->user()->shops()
            ->withCount('listings')
            ->orderBy('name')
            ->get()
            ->map(fn (Shop $shop) => [
                ...$shop->only(['id', 'name', 'slug', 'description', 'is_active']),
                'listings_count' => $shop->listings_count,
            ]);

        return Inertia::render('owner/shops/index', [
            'shops' => $shops,
        ]);
    }
}
