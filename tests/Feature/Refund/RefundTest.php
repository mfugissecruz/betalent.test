<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use Database\Factories\{GatewayFactory, TransactionFactory, UserFactory};
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\{actingAs, assertDatabaseHas, postJson};

beforeEach(function () {
    config([
        'services.gateways.gateway_1.endpoint' => 'http://gateway1.test',
        'services.gateways.gateway_2.endpoint' => 'http://gateway2.test',
    ]);
});

function fakeRefundGateway1Success(): void
{
    Http::fake([
        'http://gateway1.test/login'                 => Http::response(['token' => 'fake-token']),
        'http://gateway1.test/transactions/*/charge_back' => Http::response(['status' => 'charged_back'], 200),
    ]);
}

function fakeRefundGateway2Success(): void
{
    Http::fake([
        'http://gateway2.test/transacoes/reembolso' => Http::response(['status' => 'charged_back'], 200),
    ]);
}

test('admin can refund a transaction', function () {
    $admin       = UserFactory::new()->create(['role' => UserRole::ADMIN]);
    $gateway     = GatewayFactory::new()->create(['name' => 'gateway_1']);
    $transaction = TransactionFactory::new()->create(['gateway_id' => $gateway->id, 'status' => 'paid']);

    fakeRefundGateway1Success();

    actingAs($admin);

    postJson(route('api.transactions.refund', $transaction))
        ->assertSuccessful()
        ->assertJsonFragment(['message' => 'Transaction refunded successfully.']);
});

test('finance can refund a transaction', function () {
    $finance     = UserFactory::new()->create(['role' => UserRole::FINANCE]);
    $gateway     = GatewayFactory::new()->create(['name' => 'gateway_1']);
    $transaction = TransactionFactory::new()->create(['gateway_id' => $gateway->id, 'status' => 'paid']);

    fakeRefundGateway1Success();

    actingAs($finance);

    postJson(route('api.transactions.refund', $transaction))
        ->assertSuccessful();
});

test('manager cannot refund a transaction', function () {
    $manager     = UserFactory::new()->create(['role' => UserRole::MANAGER]);
    $gateway     = GatewayFactory::new()->create(['name' => 'gateway_1']);
    $transaction = TransactionFactory::new()->create(['gateway_id' => $gateway->id, 'status' => 'paid']);

    actingAs($manager);

    postJson(route('api.transactions.refund', $transaction))
        ->assertForbidden();
});

test('user cannot refund a transaction', function () {
    $user        = UserFactory::new()->create(['role' => UserRole::USER]);
    $gateway     = GatewayFactory::new()->create(['name' => 'gateway_1']);
    $transaction = TransactionFactory::new()->create(['gateway_id' => $gateway->id, 'status' => 'paid']);

    actingAs($user);

    postJson(route('api.transactions.refund', $transaction))
        ->assertForbidden();
});

test('refund updates transaction status to charged_back', function () {
    $admin       = UserFactory::new()->create(['role' => UserRole::ADMIN]);
    $gateway     = GatewayFactory::new()->create(['name' => 'gateway_1']);
    $transaction = TransactionFactory::new()->create(['gateway_id' => $gateway->id, 'status' => 'paid']);

    fakeRefundGateway1Success();

    actingAs($admin);

    postJson(route('api.transactions.refund', $transaction))
        ->assertSuccessful();

    assertDatabaseHas('transactions', [
        'id'     => $transaction->id,
        'status' => 'charged_back',
    ]);
});

test('refund calls the correct gateway service', function () {
    $admin       = UserFactory::new()->create(['role' => UserRole::ADMIN]);
    $gateway     = GatewayFactory::new()->create(['name' => 'gateway_2']);
    $transaction = TransactionFactory::new()->create(['gateway_id' => $gateway->id, 'status' => 'paid']);

    fakeRefundGateway2Success();

    actingAs($admin);

    postJson(route('api.transactions.refund', $transaction))
        ->assertSuccessful();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'gateway2.test'));
});

test('refund fails if transaction was already refunded', function () {
    $admin       = UserFactory::new()->create(['role' => UserRole::ADMIN]);
    $gateway     = GatewayFactory::new()->create(['name' => 'gateway_1']);
    $transaction = TransactionFactory::new()->create(['gateway_id' => $gateway->id, 'status' => 'charged_back']);

    actingAs($admin);

    postJson(route('api.transactions.refund', $transaction))
        ->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Transaction already refunded.']);
});
