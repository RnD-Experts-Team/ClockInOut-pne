<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->post('/login', [
        'name' => $user->name,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('clocking.index', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'name' => $user->name,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('admin users are redirected to admin clocking on login', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->post('/login', [
        'name' => $user->name,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.clocking', absolute: false));
});

test('store manager users are redirected to native requests on login', function () {
    $user = User::factory()->create(['role' => 'store_manager']);

    $response = $this->post('/login', [
        'name' => $user->name,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('native.requests.index', absolute: false));
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
