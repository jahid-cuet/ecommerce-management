<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShopOwnerRequest;
use App\Http\Requests\Admin\UpdateShopOwnerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopOwnerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $shopOwners = User::query()
            ->where('role', UserRole::ShopOwner)
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->withCount('shops')
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'shops_count' => $user->shops_count,
                'created_at' => $user->created_at?->toDateString(),
            ]);

        return Inertia::render('admin/shop-owners/index', [
            'shopOwners' => $shopOwners,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/shop-owners/create');
    }

    public function store(StoreShopOwnerRequest $request): RedirectResponse
    {
        $user = new User($request->validated());
        $user->role = UserRole::ShopOwner;
        // Accounts created by an admin are trusted, so no email verification step is needed.
        $user->email_verified_at = now();
        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shop owner created.')]);

        return to_route('admin.shop-owners.index');
    }

    public function edit(User $shopOwner): Response
    {
        return Inertia::render('admin/shop-owners/edit', [
            'shopOwner' => $shopOwner->only(['id', 'name', 'email']),
        ]);
    }

    public function update(UpdateShopOwnerRequest $request, User $shopOwner): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $shopOwner->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shop owner updated.')]);

        return to_route('admin.shop-owners.index');
    }

    public function destroy(User $shopOwner): RedirectResponse
    {
        if ($shopOwner->shops()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('This shop owner still owns shops. Reassign or delete their shops first.'),
            ]);

            return back();
        }

        $shopOwner->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shop owner deleted.')]);

        return to_route('admin.shop-owners.index');
    }
}
