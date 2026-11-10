<?php

use App\Models\Listing;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->owner = User::factory()->shopOwner()->create();
    $this->shop = Shop::factory()->for($this->owner, 'owner')->create(['name' => 'Gadget Hub']);
});

test('admins cannot use the shop owner panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('owner.shops.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('owner.shops.listings.index', $this->shop))->assertForbidden();
});

test('owners only see their own shops', function () {
    Shop::factory()->create(['name' => 'Someone Else']);
    Listing::factory()->for($this->shop)->count(2)->create();

    $this->actingAs($this->owner)
        ->get(route('owner.shops.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('owner/shops/index')
            ->has('shops', 1)
            ->where('shops.0.name', 'Gadget Hub')
            ->where('shops.0.listings_count', 2)
        );
});

test('owners cannot manage another owner\'s shop', function () {
    $otherShop = Shop::factory()->create();
    $listing = Listing::factory()->for($otherShop)->create();
    $product = Product::factory()->create();

    $this->actingAs($this->owner)->get(route('owner.shops.listings.index', $otherShop))->assertForbidden();
    $this->actingAs($this->owner)->get(route('owner.shops.catalog', $otherShop))->assertForbidden();
    $this->actingAs($this->owner)
        ->post(route('owner.shops.listings.store', $otherShop), ['product_id' => $product->id, 'price' => 10])
        ->assertForbidden();
    $this->actingAs($this->owner)
        ->delete(route('owner.shops.listings.destroy', [$otherShop, $listing]))
        ->assertForbidden();

    $this->assertModelExists($listing);
});

test('a listing can only be reached through its own shop', function () {
    $otherListing = Listing::factory()->create();

    $this->actingAs($this->owner)
        ->get(route('owner.shops.listings.edit', [$this->shop, $otherListing]))
        ->assertNotFound();
    $this->actingAs($this->owner)
        ->delete(route('owner.shops.listings.destroy', [$this->shop, $otherListing]))
        ->assertNotFound();

    $this->assertModelExists($otherListing);
});

test('owners can browse the whole shared catalog and see what their shop already lists', function () {
    $listed = Product::factory()->create(['name' => 'Listed Mouse']);
    Product::factory()->create(['name' => 'Unlisted Keyboard']);
    Product::factory()->create(['name' => 'Own Lamp', 'created_by' => $this->owner->id]);
    Listing::factory()->for($this->shop)->for($listed)->create();

    $this->actingAs($this->owner)
        ->get(route('owner.shops.catalog', $this->shop))
        ->assertInertia(fn (Assert $page) => $page
            ->component('owner/listings/catalog')
            ->has('products.data', 3)
        );

    $this->actingAs($this->owner)
        ->get(route('owner.shops.catalog', [$this->shop, 'search' => 'mouse']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->whereNot('products.data.0.listing_id', null)
        );

    $this->actingAs($this->owner)
        ->get(route('owner.shops.catalog', [$this->shop, 'search' => 'lamp']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.listing_id', null)
            ->where('products.data.0.added_by', 'You')
        );
});

test('owners can add a catalog product to their shop with their own price, details and thumbnail', function () {
    $product = Product::factory()->create(['price' => '1000.00', 'description' => 'Catalog text']);

    $this->actingAs($this->owner)
        ->get(route('owner.shops.listings.create', [$this->shop, 'product' => $product->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('owner/listings/create')
            ->where('product.id', $product->id)
        );

    $this->actingAs($this->owner)
        ->post(route('owner.shops.listings.store', $this->shop), [
            'product_id' => $product->id,
            'price' => '899.99',
            'description' => 'Shop text',
            'thumbnail' => UploadedFile::fake()->image('shop.png'),
        ])
        ->assertRedirect(route('owner.shops.listings.index', $this->shop))
        ->assertSessionHasNoErrors();

    $listing = Listing::sole();

    expect($listing->shop_id)->toBe($this->shop->id)
        ->and($listing->price)->toBe('899.99')
        ->and($listing->display_description)->toBe('Shop text')
        ->and($listing->thumbnail_path)->toStartWith('listings/')
        ->and($product->fresh()->price)->toBe('1000.00');

    Storage::disk('public')->assertExists($listing->thumbnail_path);
});

test('a listing falls back to the catalog details and thumbnail', function () {
    $product = Product::factory()->create([
        'description' => 'Catalog text',
        'thumbnail_path' => 'products/catalog.png',
    ]);

    $this->actingAs($this->owner)
        ->post(route('owner.shops.listings.store', $this->shop), [
            'product_id' => $product->id,
            'price' => '50',
        ])
        ->assertSessionHasNoErrors();

    $listing = Listing::sole();

    expect($listing->description)->toBeNull()
        ->and($listing->display_description)->toBe('Catalog text')
        ->and($listing->display_thumbnail_url)->toBe(Storage::disk('public')->url('products/catalog.png'));
});

test('a shop cannot list the same product twice', function () {
    $listing = Listing::factory()->for($this->shop)->create();

    $this->actingAs($this->owner)
        ->post(route('owner.shops.listings.store', $this->shop), [
            'product_id' => $listing->product_id,
            'price' => '10',
        ])
        ->assertSessionHasErrors('product_id');

    $this->actingAs($this->owner)
        ->get(route('owner.shops.listings.create', [$this->shop, 'product' => $listing->product_id]))
        ->assertRedirect(route('owner.shops.listings.edit', [$this->shop, $listing]));

    expect(Listing::count())->toBe(1);
});

test('the same product can be listed by different shops with different prices', function () {
    $product = Product::factory()->create();
    $secondShop = Shop::factory()->for($this->owner, 'owner')->create();

    foreach ([[$this->shop, '100'], [$secondShop, '120']] as [$shop, $price]) {
        $this->actingAs($this->owner)
            ->post(route('owner.shops.listings.store', $shop), ['product_id' => $product->id, 'price' => $price])
            ->assertSessionHasNoErrors();
    }

    expect($product->listings()->orderBy('price')->pluck('price')->all())->toBe(['100.00', '120.00']);
});

test('listing validation rejects bad input', function () {
    $this->actingAs($this->owner)
        ->post(route('owner.shops.listings.store', $this->shop), [
            'product_id' => 999,
            'price' => '-1',
            'thumbnail' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['product_id', 'price', 'thumbnail']);
});

test('owners can update a listing and remove its own thumbnail', function () {
    $listing = Listing::factory()->for($this->shop)->create([
        'price' => '100.00',
        'description' => 'Old text',
        'thumbnail_path' => UploadedFile::fake()->image('old.png')->store('listings', 'public'),
    ]);
    $oldPath = $listing->thumbnail_path;

    $this->actingAs($this->owner)
        ->put(route('owner.shops.listings.update', [$this->shop, $listing]), [
            'price' => '150.50',
            'description' => '',
            'remove_thumbnail' => '1',
        ])
        ->assertRedirect(route('owner.shops.listings.index', $this->shop))
        ->assertSessionHasNoErrors();

    $listing->refresh();

    expect($listing->price)->toBe('150.50')
        ->and($listing->description)->toBeNull()
        ->and($listing->thumbnail_path)->toBeNull();

    Storage::disk('public')->assertMissing($oldPath);
});

test('owners can remove a product from their shop without touching the catalog', function () {
    $listing = Listing::factory()->for($this->shop)->create([
        'thumbnail_path' => UploadedFile::fake()->image('shop.png')->store('listings', 'public'),
    ]);

    $this->actingAs($this->owner)
        ->delete(route('owner.shops.listings.destroy', [$this->shop, $listing]))
        ->assertRedirect(route('owner.shops.listings.index', $this->shop));

    $this->assertModelMissing($listing);
    $this->assertModelExists($listing->product);
    Storage::disk('public')->assertMissing($listing->thumbnail_path);
});

test('owners can search their shop listings', function () {
    Listing::factory()->for($this->shop)->for(Product::factory()->state(['name' => 'Blue Mug']))->create();
    Listing::factory()->for($this->shop)->for(Product::factory()->state(['name' => 'Red Chair']))->create();

    $this->actingAs($this->owner)
        ->get(route('owner.shops.listings.index', [$this->shop, 'search' => 'mug']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('owner/listings/index')
            ->has('listings.data', 1)
            ->where('listings.data.0.name', 'Blue Mug')
        );
});

test('deleting a catalog product or a shop removes its listings and their images', function () {
    $product = Product::factory()->create();
    $byProduct = Listing::factory()->for($product)->create([
        'thumbnail_path' => UploadedFile::fake()->image('a.png')->store('listings', 'public'),
    ]);
    $byShop = Listing::factory()->for($this->shop)->create([
        'thumbnail_path' => UploadedFile::fake()->image('b.png')->store('listings', 'public'),
    ]);

    $product->delete();
    $this->shop->delete();

    $this->assertModelMissing($byProduct);
    $this->assertModelMissing($byShop);
    Storage::disk('public')->assertMissing([$byProduct->thumbnail_path, $byShop->thumbnail_path]);
});

test('the owner dashboard shows their shops and counts', function () {
    Listing::factory()->for($this->shop)->count(3)->create();
    Product::factory()->create(['created_by' => $this->owner->id]);

    $this->actingAs($this->owner)
        ->get(route('owner.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('owner/dashboard')
            ->where('stats.shops', 1)
            ->where('stats.listings', 3)
            ->where('stats.ownProducts', 1)
            ->has('shops', 1)
        );
});
