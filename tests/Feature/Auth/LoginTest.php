<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use Database\Factories\UserFactory;

use function Pest\Laravel\postJson;

test('user should be able to login with valid credentials', function () {
    $user = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertSuccessful();
});

test('user should receive a token after successful login', function () {
    $user = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertJsonStructure([
        'token',
    ]);
});

test('user cannot login with wrong password', function () {
    $user = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ])->assertUnauthorized();
});

test('user cannot login with unregistered email', function () {
    postJson('/api/login', [
        'email'    => 'unregistered@example.com',
        'password' => 'password',
    ])->assertUnauthorized();
});

test('user should receive 429 after too many failed login attempts', function () {
    $payload = [
        'email'    => 'unregistered@example.com',
        'password' => 'wrong-password',
    ];

    foreach (range(1, 5) as $attempt) {
        postJson('/api/login', $payload);
    }

    postJson('/api/login', $payload)->assertTooManyRequests();
});
