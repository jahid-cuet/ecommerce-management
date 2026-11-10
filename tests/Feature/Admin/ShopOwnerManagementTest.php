<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('only admins can manage shop owners', function () {
    $owner = User::factory()->shopOwner()->create();

    $this->actingAs($owner)->get(route('admin.shop-owners.index'))->assertForbidden();
    $this->actingAs($owner)->post(route('admin.shop-owners.store'), [])->assertForbidden();
    $this->actingAs($owner)->delete(route('admin.shop-owners.destroy', $owner))->assertForbidden();

    auth()->logout();
    $this->get(route('admin.shop-owners.index'))->assertRedirect(route('login'));
});

test('admins can list shop owners without seeing admins', function () {
    User::factory()->shopOwner()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.shop-owners.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/shop-owners/index')
            ->has('shopOwners.data', 3)
            ->where('shopOwners.total', 3)
        );
});

test('admins can search shop owners by name or email', function () {
    User::factory()->shopOwner()->create(['name' => 'Rahim Uddin', 'email' => 'rahim@example.com']);
    User::factory()->shopOwner()->create(['name' => 'Karim', 'email' => 'karim@shop.test']);

    $this->actingAs($this->admin)
        ->get(route('admin.shop-owners.index', ['search' => 'rahim']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('shopOwners.data', 1)
            ->where('shopOwners.data.0.email', 'rahim@example.com')
            ->where('filters.search', 'rahim')
        );

    $this->actingAs($this->admin)
        ->get(route('admin.shop-owners.index', ['search' => 'shop.test']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('shopOwners.data', 1)
            ->where('shopOwners.data.0.name', 'Karim')
        );
});

test('admins can create a shop owner who can then log in', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.shop-owners.store'), [
            'name' => 'New Owner',
            'email' => 'new-owner@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => 'admin', // must be ignored
        ])
        ->assertRedirect(route('admin.shop-owners.index'))
        ->assertSessionHasNoErrors();

    $owner = User::where('email', 'new-owner@example.com')->firstOrFail();

    expect($owner->role)->toBe(UserRole::ShopOwner)
        ->and($owner->email_verified_at)->not->toBeNull()
        ->and(Hash::check('secret-password', $owner->password))->toBeTrue();

    auth()->logout();
    $this->post(route('login.store'), [
        'email' => 'new-owner@example.com',
        'password' => 'secret-password',
    ])->assertRedirect(route('dashboard', absolute: false));
});

test('creating a shop owner validates the input', function () {
    User::factory()->shopOwner()->create(['email' => 'taken@example.com']);

    $this->actingAs($this->admin)
        ->post(route('admin.shop-owners.store'), [
            'name' => '',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'different',
        ])
        ->assertSessionHasErrors(['name', 'email', 'password']);

    expect(User::where('role', UserRole::ShopOwner)->count())->toBe(1);
});

test('admins can update a shop owner and keep the password when left blank', function () {
    $owner = User::factory()->shopOwner()->create();
    $originalHash = $owner->password;

    $this->actingAs($this->admin)
        ->put(route('admin.shop-owners.update', $owner), [
            'name' => 'Renamed Owner',
            'email' => 'renamed@example.com',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('admin.shop-owners.index'))
        ->assertSessionHasNoErrors();

    $owner->refresh();

    expect($owner->name)->toBe('Renamed Owner')
        ->and($owner->email)->toBe('renamed@example.com')
        ->and($owner->password)->toBe($originalHash);
});

test('admins can set a new password for a shop owner', function () {
    $owner = User::factory()->shopOwner()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.shop-owners.update', $owner), [
            'name' => $owner->name,
            'email' => $owner->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])
        ->assertSessionHasNoErrors();

    expect(Hash::check('brand-new-password', $owner->refresh()->password))->toBeTrue();
});

test('a shop owner can keep their own email when updated', function () {
    $owner = User::factory()->shopOwner()->create(['email' => 'same@example.com']);

    $this->actingAs($this->admin)
        ->put(route('admin.shop-owners.update', $owner), [
            'name' => 'Same Email',
            'email' => 'same@example.com',
        ])
        ->assertSessionHasNoErrors();
});

test('admins can delete a shop owner', function () {
    $owner = User::factory()->shopOwner()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.shop-owners.destroy', $owner))
        ->assertRedirect(route('admin.shop-owners.index'));

    $this->assertModelMissing($owner);
});

test('shop owner routes never resolve an admin account', function () {
    $otherAdmin = User::factory()->admin()->create();

    $this->actingAs($this->admin)->get(route('admin.shop-owners.edit', $otherAdmin))->assertNotFound();
    $this->actingAs($this->admin)->put(route('admin.shop-owners.update', $otherAdmin), [
        'name' => 'Hacked',
        'email' => 'hacked@example.com',
    ])->assertNotFound();
    $this->actingAs($this->admin)->delete(route('admin.shop-owners.destroy', $otherAdmin))->assertNotFound();

    $this->assertModelExists($otherAdmin);
    expect($otherAdmin->refresh()->name)->not->toBe('Hacked');
});
