<?php

use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->owner = User::factory()->shopOwner()->create();
});

test('only admins can manage shops', function () {
    $shop = Shop::factory()->for($this->owner, 'owner')->create();

    $this->actingAs($this->owner)->get(route('admin.shops.index'))->assertForbidden();
    $this->actingAs($this->owner)->post(route('admin.shops.store'), [])->assertForbidden();
    $this->actingAs($this->owner)->delete(route('admin.shops.destroy', $shop))->assertForbidden();

    $this->assertModelExists($shop);
});

test('admins can list and search shops by shop or owner name', function () {
    $rahim = User::factory()->shopOwner()->create(['name' => 'Rahim']);
    Shop::factory()->for($rahim, 'owner')->create(['name' => 'Gadget House']);
    Shop::factory()->for($this->owner, 'owner')->create(['name' => 'Book Corner']);

    $this->actingAs($this->admin)
        ->get(route('admin.shops.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/shops/index')
            ->has('shops.data', 2)
        );

    $this->actingAs($this->admin)
        ->get(route('admin.shops.index', ['search' => 'gadget']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('shops.data', 1)
            ->where('shops.data.0.name', 'Gadget House')
            ->where('shops.data.0.owner.name', 'Rahim')
        );

    $this->actingAs($this->admin)
        ->get(route('admin.shops.index', ['search' => 'rahim']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('shops.data', 1)
            ->where('shops.data.0.name', 'Gadget House')
        );
});

test('the create form lists only shop owners', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.shops.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/shops/create')
            ->has('owners', 1)
            ->where('owners.0.id', $this->owner->id)
        );
});

test('admins can create a shop with a unique slug', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.shops.store'), [
            'owner_id' => $this->owner->id,
            'name' => 'Dhaka Gadget House',
            'description' => 'Phones and accessories.',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.shops.index'))
        ->assertSessionHasNoErrors();

    $shop = Shop::sole();

    expect($shop->owner->is($this->owner))->toBeTrue()
        ->and($shop->slug)->toBe('dhaka-gadget-house')
        ->and($shop->is_active)->toBeTrue();

    // A second shop with the same name gets its own slug.
    $this->actingAs($this->admin)->post(route('admin.shops.store'), [
        'owner_id' => $this->owner->id,
        'name' => 'Dhaka Gadget House',
    ]);

    expect(Shop::latest('id')->first()->slug)->toBe('dhaka-gadget-house-2');
});

test('an unchecked active box creates an inactive shop', function () {
    $this->actingAs($this->admin)->post(route('admin.shops.store'), [
        'owner_id' => $this->owner->id,
        'name' => 'Hidden Shop',
    ])->assertSessionHasNoErrors();

    expect(Shop::sole()->is_active)->toBeFalse();
});

test('a shop must belong to a shop owner, not an admin', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.shops.store'), [
            'owner_id' => $this->admin->id,
            'name' => 'Admin Shop',
        ])
        ->assertSessionHasErrors(['owner_id']);

    $this->actingAs($this->admin)
        ->post(route('admin.shops.store'), ['name' => ''])
        ->assertSessionHasErrors(['owner_id', 'name']);

    expect(Shop::count())->toBe(0);
});

test('admins can update a shop and reassign its owner without changing the slug', function () {
    $shop = Shop::factory()->for($this->owner, 'owner')->create(['name' => 'Old Name']);
    $originalSlug = $shop->slug;
    $newOwner = User::factory()->shopOwner()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.shops.update', $shop), [
            'owner_id' => $newOwner->id,
            'name' => 'New Name',
            'description' => 'Updated.',
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.shops.index'))
        ->assertSessionHasNoErrors();

    $shop->refresh();

    expect($shop->name)->toBe('New Name')
        ->and($shop->owner_id)->toBe($newOwner->id)
        ->and($shop->is_active)->toBeFalse()
        ->and($shop->slug)->toBe($originalSlug);
});

test('admins can delete a shop', function () {
    $shop = Shop::factory()->for($this->owner, 'owner')->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.shops.destroy', $shop))
        ->assertRedirect(route('admin.shops.index'));

    $this->assertModelMissing($shop);
});

test('a shop owner who still owns shops cannot be deleted', function () {
    Shop::factory()->for($this->owner, 'owner')->create();

    $this->actingAs($this->admin)
        ->from(route('admin.shop-owners.index'))
        ->delete(route('admin.shop-owners.destroy', $this->owner))
        ->assertRedirect(route('admin.shop-owners.index'));

    $this->assertModelExists($this->owner);
});

test('the shop owner list shows how many shops each owner has', function () {
    Shop::factory()->count(2)->for($this->owner, 'owner')->create();

    $this->actingAs($this->admin)
        ->get(route('admin.shop-owners.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shopOwners.data.0.shops_count', 2)
        );
});

test('the admin dashboard shows shop counts', function () {
    Shop::factory()->count(2)->for($this->owner, 'owner')->create();
    Shop::factory()->inactive()->for($this->owner, 'owner')->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.shops', 3)
            ->where('stats.activeShops', 2)
            ->where('stats.shopOwners', 1)
        );
});
