<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Support\Thumbnail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Catalog products created by the signed-in shop owner. They join the shared catalog,
 * so every shop can list them.
 */
class ProductController extends Controller
{
    private const THUMBNAIL_DIRECTORY = 'products';

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->where('created_by', $request->user()->id)
            ->withCount('listings')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'thumbnail_url' => $product->thumbnail_url,
                'listings_count' => $product->listings_count,
            ]);

        return Inertia::render('owner/products/index', [
            'products' => $products,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('owner/products/create', [
            'shop' => $this->ownedShop($request)?->only(['id', 'name']),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = new Product($request->safe()->only(['name', 'description', 'price']));
        $product->created_by = $request->user()->id;

        if ($request->hasFile('thumbnail')) {
            $product->thumbnail_path = Thumbnail::store($request->file('thumbnail'), self::THUMBNAIL_DIRECTORY);
        }

        $product->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product added to the catalog.')]);

        // Started from a shop's catalog page: continue straight to listing it in that shop.
        $shop = $this->ownedShop($request);

        return $shop !== null
            ? to_route('owner.shops.listings.create', ['shop' => $shop, 'product' => $product->id])
            : to_route('owner.products.index');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('owner/products/edit', [
            'product' => [
                ...$product->only(['id', 'name', 'description', 'price']),
                'thumbnail_url' => $product->thumbnail_url,
            ],
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->fill($request->safe()->only(['name', 'description', 'price']));
        $product->thumbnail_path = Thumbnail::replace(
            $product->thumbnail_path,
            $request->file('thumbnail'),
            $request->boolean('remove_thumbnail'),
            self::THUMBNAIL_DIRECTORY,
        );
        $product->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product updated.')]);

        return to_route('owner.products.index');
    }

    /**
     * The shop from the optional ?shop= parameter, only when it belongs to the signed-in owner.
     */
    private function ownedShop(Request $request): ?Shop
    {
        if (! $request->filled('shop')) {
            return null;
        }

        return $request->user()->shops()->find($request->integer('shop'));
    }
}
