<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use Database\Factories\{GatewayFactory, UserFactory};

use function Pest\Laravel\{actingAs, assertDatabaseHas, patchJson};

test('admin can activate a gateway', function () {
    $user    = UserFactory::new()->create(['role' => UserRole::ADMIN]);
    $gateway = GatewayFactory::new()->create(['is_active' => false]);

    actingAs($user);

    patchJson(route('api.gateway.activate', ['gateway' => $gateway->id]), [
        'is_active' => true,
    ])->assertSuccessful();

    assertDatabaseHas('gateways', ['is_active' => true]);
});

test('admin can deactivate a gateway', function () {
    $user    = UserFactory::new()->create(['role' => UserRole::ADMIN]);
    $gateway = GatewayFactory::new()->create(['is_active' => true]);

    actingAs($user);

    patchJson(route('api.gateway.deactivate', ['gateway' => $gateway->id]), [
        'is_active' => false,
    ])->assertSuccessful();

    assertDatabaseHas('gateways', ['is_active' => false]);
});

test('admin can update gateway priority', function () {
    $user    = UserFactory::new()->create(['role' => UserRole::ADMIN]);
    $gateway = GatewayFactory::new()->create(['priority' => 1]);

    actingAs($user);

    patchJson(route('api.gateway.priority', ['gateway' => $gateway->id]), [
        'priority' => 2,
    ])->assertSuccessful();

    assertDatabaseHas('gateways', ['priority' => 2]);
});

test('manager cannot activate a gateway', function () {
    $user    = UserFactory::new()->create(['role' => UserRole::MANAGER]);
    $gateway = GatewayFactory::new()->create(['is_active' => false]);

    actingAs($user);

    patchJson(route('api.gateway.activate', ['gateway' => $gateway->id]), [
        'is_active' => true,
    ])->assertForbidden();

    assertDatabaseHas('gateways', ['is_active' => false]);

});

test('finance cannot activate a gateway', function () {
    $user    = UserFactory::new()->create(['role' => UserRole::FINANCE]);
    $gateway = GatewayFactory::new()->create(['is_active' => false]);

    actingAs($user);

    patchJson(route('api.gateway.activate', ['gateway' => $gateway->id]), [
        'is_active' => true,
    ])->assertForbidden();

    assertDatabaseHas('gateways', ['is_active' => false]);

});

test('user cannot activate a gateway', function () {
    $user    = UserFactory::new()->create(['role' => UserRole::USER]);
    $gateway = GatewayFactory::new()->create(['is_active' => false]);

    actingAs($user);

    patchJson(route('api.gateway.activate', ['gateway' => $gateway->id]), [
        'is_active' => true,
    ])->assertForbidden();

    assertDatabaseHas('gateways', ['is_active' => false]);

});
