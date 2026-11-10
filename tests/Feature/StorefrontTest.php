<?php

use App\Models\Listing;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->shop = Shop::factory()->create(['name' => 'Gadget Hub']);
});

test('guests can open the storefront', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('store/home'));
});

test('customers only see products that an active shop sells', function () {
    $listed = Product::factory()->create(['name' => 'Listed Mouse']);
    Product::factory()->create(['name' => 'Unlisted Keyboard']);
    $inactiveShop = Shop::factory()->inactive()->create();
    $hidden = Product::factory()->create(['name' => 'Hidden Lamp']);

    Listing::factory()->for($this->shop)->for($listed)->create();
    Listing::factory()->for($inactiveShop)->for($hidden)->create();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('listings.data', 1)
            ->where('listings.data.0.name', 'Listed Mouse')
            ->has('shops', 1)
            ->where('shops.0.name', 'Gadget Hub')
        );
});

test('customers see the shop price and shop thumbnail, not the catalog ones', function () {
    $product = Product::factory()->create(['price' => '1000.00', 'thumbnail_path' => 'products/catalog.png']);
    Listing::factory()->for($this->shop)->for($product)->create([
        'price' => '850.00',
        'thumbnail_path' => 'listings/shop.png',
    ]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('listings.data.0.price', '850.00')
            ->where('listings.data.0.thumbnail_url', Storage::disk('public')->url('listings/shop.png'))
        );
});

test('customers can search products across shops', function () {
    Listing::factory()->for($this->shop)->for(Product::factory()->state(['name' => 'Blue Mug']))->create();
    Listing::factory()->for($this->shop)->for(Product::factory()->state(['name' => 'Red Chair']))->create();

    $this->get(route('home', ['search' => 'mug']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('listings.data', 1)
            ->where('listings.data.0.name', 'Blue Mug')
        );
});

test('a shop page lists only that shop\'s products', function () {
    Listing::factory()->for($this->shop)->count(2)->create();
    Listing::factory()->create();

    $this->get(route('store.shops.show', $this->shop->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/shop')
            ->where('shop.name', 'Gadget Hub')
            ->has('listings.data', 2)
        );
});

test('inactive shops are not visible to customers', function () {
    $inactive = Shop::factory()->inactive()->create();
    $listing = Listing::factory()->for($inactive)->create();

    $this->get(route('store.shops.show', $inactive->slug))->assertNotFound();
    $this->get(route('store.products.show', [$inactive->slug, $listing->product->slug]))->assertNotFound();
});

test('the product page shows the shop\'s details and other shops\' prices', function () {
    $product = Product::factory()->create(['description' => 'Catalog text']);
    Listing::factory()->for($this->shop)->for($product)->create(['price' => '500.00', 'description' => 'Shop text']);
    $cheaper = Shop::factory()->create(['name' => 'Budget Store']);
    Listing::factory()->for($cheaper)->for($product)->create(['price' => '450.00']);
    Listing::factory()->for(Shop::factory()->inactive())->for($product)->create();

    $this->get(route('store.products.show', [$this->shop->slug, $product->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/product')
            ->where('listing.price', '500.00')
            ->where('listing.description', 'Shop text')
            ->where('listing.shop.name', 'Gadget Hub')
            ->has('otherShops', 1)
            ->where('otherShops.0.shop.name', 'Budget Store')
            ->where('otherShops.0.price', '450.00')
        );
});

test('a product page falls back to the catalog details', function () {
    $product = Product::factory()->create([
        'description' => 'Catalog text',
        'thumbnail_path' => UploadedFile::fake()->image('p.png')->store('products', 'public'),
    ]);
    Listing::factory()->for($this->shop)->for($product)->create();

    $this->get(route('store.products.show', [$this->shop->slug, $product->slug]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('listing.description', 'Catalog text')
            ->where('listing.thumbnail_url', $product->thumbnail_url)
        );
});

test('a product the shop does not sell has no page in that shop', function () {
    $product = Product::factory()->create();
    Listing::factory()->for($product)->create();

    $this->get(route('store.products.show', [$this->shop->slug, $product->slug]))->assertNotFound();
});
