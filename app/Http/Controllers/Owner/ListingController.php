<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ListingRequest;
use App\Models\Listing;
use App\Models\Product;
use App\Models\Shop;
use App\Support\Thumbnail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Lets a shop owner assign catalog products to their shop with shop-specific price, details and thumbnail.
 */
class ListingController extends Controller
{
    private const THUMBNAIL_DIRECTORY = 'listings';

    public function index(Request $request, Shop $shop): Response
    {
        $search = trim((string) $request->query('search', ''));

        $listings = $shop->listings()
            ->with('product')
            ->when($search !== '', fn ($query) => $query->whereHas(
                'product',
                fn ($product) => $product->where('name', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Listing $listing) => [
                'id' => $listing->id,
                'name' => $listing->product->name,
                'price' => $listing->price,
                'catalog_price' => $listing->product->price,
                'thumbnail_url' => $listing->display_thumbnail_url,
                'has_custom_details' => $listing->description !== null,
                'has_custom_thumbnail' => $listing->thumbnail_path !== null,
            ]);

        return Inertia::render('owner/listings/index', [
            'shop' => $this->shopData($shop),
            'listings' => $listings,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Browse the shared catalog to pick products for this shop.
     */
    public function catalog(Request $request, Shop $shop): Response
    {
        $search = trim((string) $request->query('search', ''));
        $userId = $request->user()->id;

        $products = Product::query()
            ->with('creator:id,name')
            ->addSelect([
                'listing_id' => Listing::select('id')
                    ->whereColumn('product_id', 'products.id')
                    ->where('shop_id', $shop->id)
                    ->limit(1),
            ])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'thumbnail_url' => $product->thumbnail_url,
                'added_by' => match (true) {
                    $product->created_by === $userId => __('You'),
                    $product->creator === null => null,
                    default => $product->creator->name,
                },
                'listing_id' => $product->getAttribute('listing_id'),
            ]);

        return Inertia::render('owner/listings/catalog', [
            'shop' => $this->shopData($shop),
            'products' => $products,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request, Shop $shop): Response|RedirectResponse
    {
        $product = Product::findOrFail($request->integer('product'));

        $existing = $shop->listings()->where('product_id', $product->id)->first();

        if ($existing !== null) {
            Inertia::flash('toast', ['type' => 'info', 'message' => __('This product is already in your shop.')]);

            return to_route('owner.shops.listings.edit', [$shop, $existing]);
        }

        return Inertia::render('owner/listings/create', [
            'shop' => $this->shopData($shop),
            'product' => $this->productData($product),
        ]);
    }

    public function store(ListingRequest $request, Shop $shop): RedirectResponse
    {
        $listing = new Listing($request->safe()->only(['price', 'description']));
        $listing->product_id = $request->integer('product_id');

        if ($request->hasFile('thumbnail')) {
            $listing->thumbnail_path = Thumbnail::store($request->file('thumbnail'), self::THUMBNAIL_DIRECTORY);
        }

        $shop->listings()->save($listing);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product added to :shop.', ['shop' => $shop->name])]);

        return to_route('owner.shops.listings.index', $shop);
    }

    public function edit(Shop $shop, Listing $listing): Response
    {
        return Inertia::render('owner/listings/edit', [
            'shop' => $this->shopData($shop),
            'product' => $this->productData($listing->product),
            'listing' => [
                ...$listing->only(['id', 'price', 'description']),
                'thumbnail_url' => Thumbnail::url($listing->thumbnail_path),
            ],
        ]);
    }

    public function update(ListingRequest $request, Shop $shop, Listing $listing): RedirectResponse
    {
        $listing->fill($request->safe()->only(['price', 'description']));
        $listing->thumbnail_path = Thumbnail::replace(
            $listing->thumbnail_path,
            $request->file('thumbnail'),
            $request->boolean('remove_thumbnail'),
            self::THUMBNAIL_DIRECTORY,
        );
        $listing->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Listing updated.')]);

        return to_route('owner.shops.listings.index', $shop);
    }

    public function destroy(Shop $shop, Listing $listing): RedirectResponse
    {
        $listing->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product removed from :shop.', ['shop' => $shop->name])]);

        return to_route('owner.shops.listings.index', $shop);
    }

    /**
     * @return array<string, mixed>
     */
    private function shopData(Shop $shop): array
    {
        return $shop->only(['id', 'name', 'slug', 'is_active']);
    }

    /**
     * The catalog values a listing falls back to.
     *
     * @return array<string, mixed>
     */
    private function productData(Product $product): array
    {
        return [
            ...$product->only(['id', 'name', 'description', 'price']),
            'thumbnail_url' => $product->thumbnail_url,
        ];
    }
}
