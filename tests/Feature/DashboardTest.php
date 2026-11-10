<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('admins are sent to the admin dashboard', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('shop owners are sent to the shop owner dashboard', function () {
    $this->actingAs(User::factory()->shopOwner()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('owner.dashboard'));
});
