<?php

use App\Models\User;

beforeEach(function () {
    config([
        'services.turnstile.secret' => 'dummy-secret',
        'services.turnstile.bypass_secret' => 'valid-test-token',
    ]);
});

test('login fails when turnstile token is invalid', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'cf-turnstile-response' => 'invalid-token',
    ]);

    $response->assertSessionHasErrors('turnstile');
    $this->assertGuest();
});

test('login succeeds when turnstile token is provided', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'cf-turnstile-response' => 'valid-test-token',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration fails when turnstile token is invalid', function () {
    $response = $this->post(route('register'), [
        'name' => 'Turnstile Test',
        'email' => 'turnstile@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'cf-turnstile-response' => 'invalid-token',
    ]);

    $response->assertSessionHasErrors('cf-turnstile-response');
    $this->assertGuest();
});

test('registration succeeds when turnstile token is provided', function () {
    $response = $this->post(route('register'), [
        'name' => 'Turnstile Test User',
        'email' => 'turnstile-success@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'cf-turnstile-response' => 'valid-test-token',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'turnstile-success@example.com',
    ]);
});
