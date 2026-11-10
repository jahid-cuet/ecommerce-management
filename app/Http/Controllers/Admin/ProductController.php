<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Support\Thumbnail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    private const THUMBNAIL_DIRECTORY = 'products';

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->with('creator:id,name,role')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'thumbnail_url' => $product->thumbnail_url,
                'creator' => $product->creator ? [
                    'name' => $product->creator->name,
                    'role' => $product->creator->role->label(),
                ] : null,
                'created_at' => $product->created_at?->toDateString(),
            ]);

        return Inertia::render('admin/products/index', [
            'products' => $products,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/create');
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = new Product($request->safe()->only(['name', 'description', 'price']));
        $product->created_by = $request->user()->id;

        if ($request->hasFile('thumbnail')) {
            $product->thumbnail_path = Thumbnail::store($request->file('thumbnail'), self::THUMBNAIL_DIRECTORY);
        }

        $product->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product created.')]);

        return to_route('admin.products.index');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('admin/products/edit', [
            'product' => [
                ...$product->only(['id', 'name', 'description', 'price']),
                'thumbnail_url' => $product->thumbnail_url,
            ],
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->fill($request->safe()->only(['name', 'description', 'price']));

        if ($request->hasFile('thumbnail') || $request->boolean('remove_thumbnail')) {
            Thumbnail::delete($product->thumbnail_path);
            $product->thumbnail_path = $request->hasFile('thumbnail')
                ? Thumbnail::store($request->file('thumbnail'), self::THUMBNAIL_DIRECTORY)
                : null;
        }

        $product->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product updated.')]);

        return to_route('admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product deleted.')]);

        return to_route('admin.products.index');
    }
}
