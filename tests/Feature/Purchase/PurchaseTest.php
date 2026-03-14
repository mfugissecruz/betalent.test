<?php

declare(strict_types = 1);

use Database\Factories\{ClientFactory, GatewayFactory, ProductFactory};
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseHas, postJson};

beforeEach(function () {
    config([
        'services.gateways.gateway_1.endpoint' => 'http://gateway1.test',
        'services.gateways.gateway_2.endpoint' => 'http://gateway2.test',
    ]);
});

function purchasePayload(array $products): array
{
    return [
        'client'   => ['name' => 'John Doe', 'email' => 'john@test.com'],
        'card'     => ['number' => '5569000000006063', 'cvv' => '010'],
        'products' => $products,
    ];
}

function fakeGateway1Success(): void
{
    Http::fake([
        'http://gateway1.test/login'        => Http::response(['token' => 'fake-token']),
        'http://gateway1.test/transactions' => function (\Illuminate\Http\Client\Request $request) {
            if ($request->method() === 'POST') {
                return Http::response(['id' => 'gw1-ext-id'], 201);
            }

            return Http::response(['data' => [['id' => 'gw1-ext-id', 'status' => 'paid']]], 200);
        },
    ]);
}

function fakeGateway1FailGateway2Success(): void
{
    Http::fake([
        'http://gateway1.test/login'        => Http::response(['token' => 'fake-token']),
        'http://gateway1.test/transactions' => Http::response([], 500),
        'http://gateway2.test/transacoes'   => function (\Illuminate\Http\Client\Request $request) {
            if ($request->method() === 'POST') {
                return Http::response(['id' => 'gw2-ext-id'], 201);
            }

            return Http::response(['data' => [['id' => 'gw2-ext-id', 'status' => 'paid']]], 200);
        },
    ]);
}

function fakeAllGatewaysFail(): void
{
    Http::fake([
        'http://gateway1.test/login'        => Http::response(['token' => 'fake-token']),
        'http://gateway1.test/transactions' => Http::response([], 500),
        'http://gateway2.test/transacoes'   => Http::response([], 500),
    ]);
}

test('purchase is created successfully via gateway 1', function () {
    $gateway1 = GatewayFactory::new()->create(['name' => 'gateway_1', 'priority' => 1]);
    GatewayFactory::new()->create(['name' => 'gateway_2', 'priority' => 2]);
    $product = ProductFactory::new()->create(['amount' => 1]);

    fakeGateway1Success();

    postJson('/api/purchase', purchasePayload([['id' => $product->id, 'quantity' => 2]]))
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => ['id', 'external_id', 'status', 'amount', 'card_last_numbers', 'gateway', 'products', 'created_at'],
        ]);

    assertDatabaseHas('transactions', [
        'gateway_id'  => $gateway1->id,
        'external_id' => 'gw1-ext-id',
        'status'      => 'paid',
    ]);
});

test('purchase falls back to gateway 2 when gateway 1 fails', function () {
    GatewayFactory::new()->create(['name' => 'gateway_1', 'priority' => 1]);
    $gateway2 = GatewayFactory::new()->create(['name' => 'gateway_2', 'priority' => 2]);
    $product  = ProductFactory::new()->create(['amount' => 1]);

    fakeGateway1FailGateway2Success();

    postJson('/api/purchase', purchasePayload([['id' => $product->id, 'quantity' => 1]]))
        ->assertSuccessful();

    assertDatabaseHas('transactions', [
        'gateway_id'  => $gateway2->id,
        'external_id' => 'gw2-ext-id',
    ]);
});

test('purchase fails when all gateways fail', function () {
    GatewayFactory::new()->create(['name' => 'gateway_1', 'priority' => 1]);
    GatewayFactory::new()->create(['name' => 'gateway_2', 'priority' => 2]);
    $product = ProductFactory::new()->create(['amount' => 1]);

    fakeAllGatewaysFail();

    postJson('/api/purchase', purchasePayload([['id' => $product->id, 'quantity' => 1]]))
        ->assertStatus(502);

    assertDatabaseCount('transactions', 0);
});

test('purchase calculates amount from products and quantities', function () {
    GatewayFactory::new()->create(['name' => 'gateway_1', 'priority' => 1]);
    $product1 = ProductFactory::new()->create(['amount' => 1]);
    $product2 = ProductFactory::new()->create(['amount' => 2]);

    fakeGateway1Success();

    postJson('/api/purchase', purchasePayload([
        ['id' => $product1->id, 'quantity' => 3],
        ['id' => $product2->id, 'quantity' => 1],
    ]))->assertSuccessful();

    assertDatabaseHas('transactions', ['amount' => 500]);
});

test('purchase creates client if not exists', function () {
    GatewayFactory::new()->create(['name' => 'gateway_1', 'priority' => 1]);
    $product = ProductFactory::new()->create(['amount' => 1]);

    fakeGateway1Success();

    postJson('/api/purchase', purchasePayload([['id' => $product->id, 'quantity' => 1]]))
        ->assertSuccessful();

    assertDatabaseHas('clients', ['email' => 'john@test.com', 'name' => 'John Doe']);
    assertDatabaseCount('clients', 1);
});

test('purchase reuses existing client by email', function () {
    GatewayFactory::new()->create(['name' => 'gateway_1', 'priority' => 1]);
    $product = ProductFactory::new()->create(['amount' => 1]);
    $client  = ClientFactory::new()->create(['email' => 'john@test.com']);

    fakeGateway1Success();

    postJson('/api/purchase', purchasePayload([['id' => $product->id, 'quantity' => 1]]))
        ->assertSuccessful();

    assertDatabaseCount('clients', 1);
    assertDatabaseHas('transactions', ['client_id' => $client->id]);
});

test('purchase persists transaction products with correct quantity', function () {
    GatewayFactory::new()->create(['name' => 'gateway_1', 'priority' => 1]);
    $product = ProductFactory::new()->create(['amount' => 1]);

    fakeGateway1Success();

    postJson('/api/purchase', purchasePayload([['id' => $product->id, 'quantity' => 3]]))
        ->assertSuccessful();

    assertDatabaseHas('transaction_products', [
        'product_id' => $product->id,
        'quantity'   => 3,
    ]);
});

test('purchase fails with invalid product id', function () {
    postJson('/api/purchase', purchasePayload([['id' => 9999, 'quantity' => 1]]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['products.0.id']);
});

test('purchase fails with missing card fields', function () {
    $product = ProductFactory::new()->create();

    postJson('/api/purchase', [
        'client'   => ['name' => 'John', 'email' => 'john@test.com'],
        'products' => [['id' => $product->id, 'quantity' => 1]],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['card']);
});
