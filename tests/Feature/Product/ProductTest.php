<?php

declare(strict_types = 1);

namespace Tests\Feature\Product;

use App\Enum\UserRole;
use Database\Factories\{ProductFactory, UserFactory};

use function Pest\Laravel\{actingAs, assertDatabaseCount, assertDatabaseHas, deleteJson, getJson, postJson, putJson};

test('admin can create a product', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    actingAs($admin);

    $productData = [
        'name'   => 'Test Product',
        'amount' => 99.99,
    ];

    postJson('/api/products', $productData)
        ->assertSuccessful()
        ->assertJsonFragment(['name' => 'Test Product']);

    assertDatabaseCount('products', 1);

    assertDatabaseHas('products', [
        "id"     => 1,
        'name'   => 'Test Product',
        'amount' => 9999,
    ]);
});

test('products list response is paginated', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    ProductFactory::new()->count(3)->create();

    actingAs($admin);

    getJson('/api/products')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta'  => ['current_page', 'per_page', 'total'],
        ]);
});

test('manager can create a product', function () {
    $manager = UserFactory::new()->create(['role' => UserRole::MANAGER->value]);

    actingAs($manager);

    $productData = [
        'name'   => 'Test Product',
        'amount' => 99.99,
    ];

    postJson('/api/products', $productData)
        ->assertSuccessful()
        ->assertJsonFragment(['name' => 'Test Product']);

    assertDatabaseCount('products', 1);

    assertDatabaseHas('products', [
        "id"     => 1,
        'name'   => 'Test Product',
        'amount' => 9999,
    ]);
});

test('finance can create a product', function () {
    $finance = UserFactory::new()->create(['role' => UserRole::FINANCE->value]);

    actingAs($finance);

    $productData = [
        'name'   => 'Test Product',
        'amount' => 99.99,
    ];

    postJson('/api/products', $productData)
        ->assertSuccessful()
        ->assertJsonFragment(['name' => 'Test Product']);

    assertDatabaseCount('products', 1);

    assertDatabaseHas('products', [
        "id"     => 1,
        'name'   => 'Test Product',
        'amount' => 9999,
    ]);
});

test('user cannot create a product', function () {
    $user = UserFactory::new()->create(['role' => UserRole::USER->value]);

    actingAs($user);

    $productData = [
        'name'   => 'Test Product',
        'amount' => 99.99,
    ];

    postJson('/api/products', $productData)
        ->assertForbidden();
});

test('admin can update a product', function () {
    $admin   = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $product = ProductFactory::new()->create();

    actingAs($admin);

    $updateData = [
        'name'   => 'Updated Product',
        'amount' => 149.99,
    ];

    putJson("/api/products/{$product->id}", $updateData)
        ->assertSuccessful()
        ->assertJsonFragment(['name' => 'Updated Product']);

    assertDatabaseHas('products', [
        "id"     => $product->id,
        'name'   => 'Updated Product',
        'amount' => 14999,
    ]);
});

test('admin can delete a product', function () {
    $admin   = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $product = ProductFactory::new()->create();

    actingAs($admin);

    deleteJson("/api/products/{$product->id}")
        ->assertSuccessful()
        ->assertJsonFragment(['message' => 'Product deleted successfully.']);

    assertDatabaseCount('products', 0);
});

test('all authenticated users can list products', function () {
    $admin   = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $manager = UserFactory::new()->create(['role' => UserRole::MANAGER->value]);
    $finance = UserFactory::new()->create(['role' => UserRole::FINANCE->value]);
    $user    = UserFactory::new()->create(['role' => UserRole::USER->value]);

    ProductFactory::new()->count(2)->create();

    actingAs($admin);
    getJson('/api/products')->assertSuccessful();

    actingAs($manager);
    getJson('/api/products')->assertSuccessful();

    actingAs($finance);
    getJson('/api/products')->assertSuccessful();

    actingAs($user);
    getJson('/api/products')->assertSuccessful();
});

test('unauthenticated request cannot list products', function () {
    getJson('/api/products')->assertUnauthorized();
});

test('create product fails without required fields', function () {
    $admin = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);

    actingAs($admin);

    postJson('/api/products', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'amount']);
});
