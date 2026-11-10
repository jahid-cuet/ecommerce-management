<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admins can view the admin dashboard', function () {
    User::factory()->shopOwner()->count(2)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('stats.shopOwners', 2)
        );
});

test('shop owners cannot view the admin panel', function () {
    $this->actingAs(User::factory()->shopOwner()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('shop owners can view the shop owner dashboard', function () {
    $this->actingAs(User::factory()->shopOwner()->create())
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('owner/dashboard'));
});

test('admins cannot view the shop owner panel', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('owner.dashboard'))
        ->assertForbidden();
});

test('guests cannot view either panel', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('owner.dashboard'))->assertRedirect(route('login'));
});

test('public registration is disabled', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', [
        'name' => 'Someone',
        'email' => 'someone@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->assertDatabaseMissing('users', ['email' => 'someone@example.com']);
});

test('the role cannot be mass assigned', function () {
    $user = new User(['name' => 'X', 'email' => 'x@example.com', 'password' => 'secret', 'role' => 'admin']);

    expect($user->role)->toBeNull();
});
