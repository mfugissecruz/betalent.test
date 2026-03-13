<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use App\Models\User;
use Database\Factories\UserFactory;

use function Pest\Laravel\{actingAs, deleteJson, getJson, postJson, putJson};

test('admin can list users', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    UserFactory::new()->count(3)->create();

    actingAs($admin);

    getJson('/api/users')
        ->assertSuccessful()
        ->assertJsonCount(4, 'data');
});

test('users list response is paginated', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    UserFactory::new()->count(3)->create();

    actingAs($admin);

    getJson('/api/users')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta'  => ['current_page', 'per_page', 'total'],
        ]);
});

test('manager can list users', function () {
    $manager = UserFactory::new()->create(['role' => UserRole::MANAGER->value]);
    UserFactory::new()->count(2)->create();

    actingAs($manager);

    getJson('/api/users')->assertSuccessful();
});

test('finance cannot list users', function () {
    $finance = UserFactory::new()->create(['role' => UserRole::FINANCE->value]);

    actingAs($finance);

    getJson('/api/users')->assertForbidden();
});

test('user cannot list users', function () {
    $user = UserFactory::new()->create(['role' => UserRole::USER->value]);

    actingAs($user);

    getJson('/api/users')->assertForbidden();
});

test('admin can create a user', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    actingAs($admin);

    postJson('/api/users', [
        'name'     => 'New User',
        'email'    => 'newuser@example.com',
        'password' => 'password123',
        'role'     => UserRole::USER->value,
    ])
        ->assertSuccessful()
        ->assertJsonFragment(['email' => 'newuser@example.com']);

    expect(User::where('email', 'newuser@example.com')->exists())->toBeTrue();
});

test('manager can create a user', function () {
    $manager = UserFactory::new()->create(['role' => UserRole::MANAGER->value]);

    actingAs($manager);

    postJson('/api/users', [
        'name'     => 'New User',
        'email'    => 'newuser@example.com',
        'password' => 'password123',
        'role'     => UserRole::USER->value,
    ])->assertSuccessful();
});

test('finance cannot create a user', function () {
    $finance = UserFactory::new()->create(['role' => UserRole::FINANCE->value]);

    actingAs($finance);

    postJson('/api/users', [
        'name'     => 'New User',
        'email'    => 'newuser@example.com',
        'password' => 'password123',
        'role'     => UserRole::USER->value,
    ])->assertForbidden();
});

test('admin can update a user', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $user  = UserFactory::new()->create();

    actingAs($admin);

    putJson("/api/users/{$user->id}", ['name' => 'Updated Name'])
        ->assertSuccessful()
        ->assertJsonFragment(['name' => 'Updated Name']);
});

test('admin can delete a user', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $user  = UserFactory::new()->create();

    actingAs($admin);

    deleteJson("/api/users/{$user->id}")
        ->assertSuccessful()
        ->assertJsonFragment(['message' => 'User deleted successfully.']);

    expect(User::find($user->id))->toBeNull();
});

test('create user fails with duplicate email', function () {
    $admin    = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $existing = UserFactory::new()->create();

    actingAs($admin);

    postJson('/api/users', [
        'name'     => 'Another User',
        'email'    => $existing->email,
        'password' => 'password123',
        'role'     => UserRole::USER->value,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('create user fails with invalid role', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    actingAs($admin);

    postJson('/api/users', [
        'name'     => 'New User',
        'email'    => 'newuser@example.com',
        'password' => 'password123',
        'role'     => 'INVALID_ROLE',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['role']);
});
