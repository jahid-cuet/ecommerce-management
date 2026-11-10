<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('only admins can manage products from the admin panel', function () {
    $owner = User::factory()->shopOwner()->create();
    $product = Product::factory()->create();

    $this->actingAs($owner)->get(route('admin.products.index'))->assertForbidden();
    $this->actingAs($owner)->post(route('admin.products.store'), [])->assertForbidden();
    $this->actingAs($owner)->delete(route('admin.products.destroy', $product))->assertForbidden();

    $this->assertModelExists($product);
});

test('admins can list and search products', function () {
    Product::factory()->create(['name' => 'Wireless Mouse']);
    Product::factory()->create(['name' => 'Cotton T-Shirt']);

    $this->actingAs($this->admin)
        ->get(route('admin.products.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/products/index')
            ->has('products.data', 2)
        );

    $this->actingAs($this->admin)
        ->get(route('admin.products.index', ['search' => 'mouse']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Wireless Mouse')
            ->where('products.data.0.creator.role', 'Admin')
        );
});

test('admins can create a product with a thumbnail', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            'name' => 'Wireless Mouse',
            'description' => 'A quiet mouse.',
            'price' => '1250.50',
            'thumbnail' => UploadedFile::fake()->image('mouse.jpg', 400, 400),
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHasNoErrors();

    $product = Product::sole();

    expect($product->name)->toBe('Wireless Mouse')
        ->and($product->slug)->toBe('wireless-mouse')
        ->and($product->price)->toBe('1250.50')
        ->and($product->created_by)->toBe($this->admin->id)
        ->and($product->thumbnail_url)->toContain('/storage/products/');

    Storage::disk('public')->assertExists($product->thumbnail_path);
});

test('a thumbnail is optional', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), ['name' => 'Plain Product', 'price' => '99'])
        ->assertSessionHasNoErrors();

    expect(Product::sole()->thumbnail_path)->toBeNull();
});

test('creating a product validates the input', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            'name' => '',
            'price' => '-5',
            'thumbnail' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['name', 'price', 'thumbnail']);

    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            'name' => 'Too Big',
            'price' => '10',
            'thumbnail' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ])
        ->assertSessionHasErrors(['thumbnail']);

    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), ['name' => 'Bad Price', 'price' => '10.999'])
        ->assertSessionHasErrors(['price']);

    expect(Product::count())->toBe(0);
});

test('admins can update a product and replace its thumbnail', function () {
    $oldPath = UploadedFile::fake()->image('old.jpg')->store('products', 'public');
    $product = Product::factory()->create(['name' => 'Old Name', 'thumbnail_path' => $oldPath]);
    $originalSlug = $product->slug;

    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $product), [
            'name' => 'New Name',
            'description' => 'Updated.',
            'price' => '500',
            'thumbnail' => UploadedFile::fake()->image('new.png'),
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHasNoErrors();

    $product->refresh();

    expect($product->name)->toBe('New Name')
        ->and($product->price)->toBe('500.00')
        ->and($product->slug)->toBe($originalSlug)
        ->and($product->thumbnail_path)->not->toBe($oldPath);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($product->thumbnail_path);
});

test('updating without a new image keeps the current thumbnail', function () {
    $path = UploadedFile::fake()->image('keep.jpg')->store('products', 'public');
    $product = Product::factory()->create(['thumbnail_path' => $path]);

    $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
        'name' => 'Same Image',
        'price' => '10',
    ])->assertSessionHasNoErrors();

    expect($product->refresh()->thumbnail_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('admins can remove a product thumbnail', function () {
    $path = UploadedFile::fake()->image('remove.jpg')->store('products', 'public');
    $product = Product::factory()->create(['thumbnail_path' => $path]);

    $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
        'name' => $product->name,
        'price' => '10',
        'remove_thumbnail' => '1',
    ])->assertSessionHasNoErrors();

    expect($product->refresh()->thumbnail_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('deleting a product also deletes its thumbnail file', function () {
    $path = UploadedFile::fake()->image('gone.jpg')->store('products', 'public');
    $product = Product::factory()->create(['thumbnail_path' => $path]);

    $this->actingAs($this->admin)
        ->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.products.index'));

    $this->assertModelMissing($product);
    Storage::disk('public')->assertMissing($path);
});

test('products stay in the catalog when their creator is deleted', function () {
    $owner = User::factory()->shopOwner()->create();
    $product = Product::factory()->create(['created_by' => $owner->id]);

    $owner->delete();

    expect($product->refresh()->created_by)->toBeNull();
});

test('the admin dashboard counts catalog products', function () {
    Product::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('stats.products', 3));
});
