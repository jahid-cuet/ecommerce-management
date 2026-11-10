<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The public storefront. Customers only see products through listings of active shops,
 * so a catalog product that no shop sells is never visible here.
 */
class StorefrontController extends Controller
{
    public function home(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $shops = Shop::query()
            ->active()
            ->has('listings')
            ->withCount('listings')
            ->orderBy('name')
            ->get()
            ->map(fn (Shop $shop) => [
                ...$shop->only(['name', 'slug', 'description']),
                'listings_count' => $shop->listings_count,
            ]);

        $listings = $this->visibleListings($search)
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Listing $listing) => $this->listingCard($listing));

        return Inertia::render('store/home', [
            'shops' => $shops,
            'listings' => $listings,
            'filters' => ['search' => $search],
        ]);
    }

    public function shop(Request $request, Shop $shop): Response
    {
        abort_unless($shop->is_active, 404);

        $search = trim((string) $request->query('search', ''));

        $listings = $this->visibleListings($search)
            ->where('shop_id', $shop->id)
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Listing $listing) => $this->listingCard($listing));

        return Inertia::render('store/shop', [
            'shop' => $shop->only(['name', 'slug', 'description']),
            'listings' => $listings,
            'filters' => ['search' => $search],
        ]);
    }

    public function product(Shop $shop, Product $product): Response
    {
        abort_unless($shop->is_active, 404);

        // The product page only exists when this shop sells the product.
        $listing = $shop->listings()->where('product_id', $product->id)->firstOrFail();
        $listing->setRelation('shop', $shop)->setRelation('product', $product);

        // The same product in other active shops, each with its own price.
        $otherShops = $product->listings()
            ->with('shop:id,name,slug')
            ->where('shop_id', '!=', $shop->id)
            ->whereHas('shop', fn (Builder $query) => $query->active())
            ->orderBy('price')
            ->get()
            ->map(fn (Listing $other) => [
                'shop' => $other->shop->only(['name', 'slug']),
                'price' => $other->price,
            ]);

        return Inertia::render('store/product', [
            'listing' => [
                ...$this->listingCard($listing),
                'description' => $listing->display_description,
            ],
            'otherShops' => $otherShops,
        ]);
    }

    /**
     * Listings customers may see: only those of active shops.
     *
     * @return Builder<Listing>
     */
    private function visibleListings(string $search): Builder
    {
        return Listing::query()
            ->with(['product', 'shop:id,name,slug'])
            ->whereHas('shop', fn (Builder $query) => $query->active())
            ->when($search !== '', fn (Builder $query) => $query->whereHas(
                'product',
                fn (Builder $product) => $product->where('name', 'like', "%{$search}%")
            ));
    }

    /**
     * What a customer sees for a product in a shop: the shop's price, image and details.
     *
     * @return array<string, mixed>
     */
    private function listingCard(Listing $listing): array
    {
        return [
            'id' => $listing->id,
            'name' => $listing->product->name,
            'product_slug' => $listing->product->slug,
            'price' => $listing->price,
            'thumbnail_url' => $listing->display_thumbnail_url,
            'shop' => $listing->shop->only(['name', 'slug']),
        ];
    }
}
