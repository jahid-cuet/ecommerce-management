<?php

use App\Enums\UserRole;
use App\Models\Listing;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
});

test('the demo seeder can be run more than once', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::where('role', UserRole::Admin)->count())->toBe(1)
        ->and(User::where('role', UserRole::ShopOwner)->count())->toBe(2)
        ->and(Shop::count())->toBe(3)
        ->and(Product::count())->toBe(11)
        ->and(Listing::count())->toBe(13);

    Storage::disk('public')->assertExists('products/demo-wireless-mouse.svg');
});

test('the demo accounts can log in', function (string $email) {
    $this->seed(DatabaseSeeder::class);

    $this->post(route('login.store'), ['email' => $email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
})->with(['admin@example.com', 'owner@example.com', 'owner2@example.com']);

test('the demo storefront hides unlisted products and the inactive shop', function () {
    $this->seed(DatabaseSeeder::class);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('shops', 2)
            ->has('listings.data', 12)
            ->where('listings.data', fn ($listings) => collect($listings)
                ->pluck('name')
                ->intersect(['Steel Water Bottle', 'Notebook Set'])
                ->isEmpty())
        );

    $this->get(route('store.shops.show', 'book-corner'))->assertNotFound();
});
