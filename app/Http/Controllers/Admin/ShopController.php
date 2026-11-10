<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $shops = Shop::query()
            ->with('owner:id,name,email')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', "%{$search}%"))
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Shop $shop) => [
                'id' => $shop->id,
                'name' => $shop->name,
                'slug' => $shop->slug,
                'is_active' => $shop->is_active,
                'owner' => $shop->owner->only(['id', 'name', 'email']),
                'created_at' => $shop->created_at?->toDateString(),
            ]);

        return Inertia::render('admin/shops/index', [
            'shops' => $shops,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/shops/create', [
            'owners' => $this->ownerOptions(),
        ]);
    }

    public function store(ShopRequest $request): RedirectResponse
    {
        Shop::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shop created.')]);

        return to_route('admin.shops.index');
    }

    public function edit(Shop $shop): Response
    {
        return Inertia::render('admin/shops/edit', [
            'shop' => $shop->only(['id', 'owner_id', 'name', 'slug', 'description', 'is_active']),
            'owners' => $this->ownerOptions(),
        ]);
    }

    public function update(ShopRequest $request, Shop $shop): RedirectResponse
    {
        $shop->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shop updated.')]);

        return to_route('admin.shops.index');
    }

    public function destroy(Shop $shop): RedirectResponse
    {
        $shop->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shop deleted.')]);

        return to_route('admin.shops.index');
    }

    /**
     * Shop owners to choose from in the shop form.
     *
     * @return array<int, array{id: int, name: string, email: string}>
     */
    private function ownerOptions(): array
    {
        return User::query()
            ->where('role', UserRole::ShopOwner)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email])
            ->all();
    }
}
