<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use Database\Factories\UserFactory;

use function Pest\Laravel\postJson;

test('successfull login with valid credentials', function () {
    $user = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertSuccessful();
});

test('login returns token with valid credentials', function () {
    $user = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertJsonStructure([
        'token',
    ]);
});

test('login fails with wrong password', function () {
    $user = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ])->assertUnauthorized();
});

test('login fails with unregistered email', function () {
    postJson('/api/login', [
        'email'    => 'unregistered@example.com',
        'password' => 'password',
    ])->assertUnauthorized();
});

test('login is rate limited after 5 attempts', function () {
    $payload = [
        'email'    => 'unregistered@example.com',
        'password' => 'wrong-password',
    ];

    foreach (range(1, 5) as $attempt) {
        postJson('/api/login', $payload);
    }

    postJson('/api/login', $payload)->assertTooManyRequests();
});
