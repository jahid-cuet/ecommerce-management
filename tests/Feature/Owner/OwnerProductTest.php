<?php

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->owner = User::factory()->shopOwner()->create();
});

test('products created by an owner join the shared catalog', function () {
    $this->actingAs($this->owner)
        ->post(route('owner.products.store'), [
            'name' => 'Handmade Lamp',
            'price' => '2500',
            'thumbnail' => UploadedFile::fake()->image('lamp.jpg'),
        ])
        ->assertRedirect(route('owner.products.index'))
        ->assertSessionHasNoErrors();

    $product = Product::sole();

    expect($product->created_by)->toBe($this->owner->id);
    Storage::disk('public')->assertExists($product->thumbnail_path);

    // Visible in the admin catalog and in any other owner's shop catalog.
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.products.index'))
        ->assertInertia(fn (Assert $page) => $page->where('products.data.0.creator.role', 'Shop Owner'));

    $otherShop = Shop::factory()->create();
    $this->actingAs($otherShop->owner)
        ->get(route('owner.shops.catalog', $otherShop))
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.name', 'Handmade Lamp')
            ->where('products.data.0.added_by', $this->owner->name)
        );
});

test('creating a product from a shop catalog continues to listing it in that shop', function () {
    $shop = Shop::factory()->for($this->owner, 'owner')->create();

    $this->actingAs($this->owner)
        ->post(route('owner.products.store', ['shop' => $shop->id]), ['name' => 'Desk', 'price' => '10'])
        ->assertRedirect(route('owner.shops.listings.create', ['shop' => $shop, 'product' => Product::sole()->id]));
});

test('another owner\'s shop in the query string is ignored', function () {
    $otherShop = Shop::factory()->create();

    $this->actingAs($this->owner)
        ->post(route('owner.products.store', ['shop' => $otherShop->id]), ['name' => 'Desk', 'price' => '10'])
        ->assertRedirect(route('owner.products.index'));
});

test('owners only see and edit the products they created', function () {
    $own = Product::factory()->create(['created_by' => $this->owner->id, 'name' => 'Own Product']);
    $other = Product::factory()->create();

    $this->actingAs($this->owner)
        ->get(route('owner.products.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('owner/products/index')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Own Product')
        );

    $this->actingAs($this->owner)->get(route('owner.products.edit', $own))->assertOk();
    $this->actingAs($this->owner)->get(route('owner.products.edit', $other))->assertForbidden();
    $this->actingAs($this->owner)
        ->put(route('owner.products.update', $other), ['name' => 'Hacked', 'price' => '1'])
        ->assertForbidden();

    expect($other->fresh()->name)->not->toBe('Hacked');
});

test('owners can update their product and replace its thumbnail', function () {
    $product = Product::factory()->create([
        'created_by' => $this->owner->id,
        'thumbnail_path' => UploadedFile::fake()->image('old.png')->store('products', 'public'),
    ]);
    $oldPath = $product->thumbnail_path;

    $this->actingAs($this->owner)
        ->put(route('owner.products.update', $product), [
            'name' => 'Renamed',
            'price' => '75.25',
            'thumbnail' => UploadedFile::fake()->image('new.png'),
        ])
        ->assertRedirect(route('owner.products.index'))
        ->assertSessionHasNoErrors();

    $product->refresh();

    expect($product->name)->toBe('Renamed')
        ->and($product->price)->toBe('75.25')
        ->and($product->thumbnail_path)->not->toBe($oldPath);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($product->thumbnail_path);
});

test('shop owners cannot delete catalog products', function () {
    $product = Product::factory()->create(['created_by' => $this->owner->id]);

    $this->actingAs($this->owner)->delete("/owner/products/{$product->id}")->assertMethodNotAllowed();

    $this->assertModelExists($product);
});
