<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use Database\Factories\{ClientFactory, TransactionFactory, UserFactory};

use function Pest\Laravel\{actingAs, getJson};

test('all authenticated users can list clients', function () {
    $admin   = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $manager = UserFactory::new()->create(['role' => UserRole::MANAGER->value]);
    $finance = UserFactory::new()->create(['role' => UserRole::FINANCE->value]);
    $user    = UserFactory::new()->create(['role' => UserRole::USER->value]);

    collect([$admin, $manager, $finance, $user])->each(function ($agent) {
        actingAs($agent);
        getJson(route('api.clients.index'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'created_at'],
                ],
            ]);
    });
});

test('all authenticated users can view client detail', function () {
    $admin   = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $manager = UserFactory::new()->create(['role' => UserRole::MANAGER->value]);
    $finance = UserFactory::new()->create(['role' => UserRole::FINANCE->value]);
    $user    = UserFactory::new()->create(['role' => UserRole::USER->value]);

    $client = ClientFactory::new()->create();

    collect([$admin, $manager, $finance, $user])->each(function ($agent) use ($client) {
        actingAs($agent);
        getJson(route('api.clients.show', $client))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'created_at', 'transactions'],
            ]);
    });
});

test('client detail includes transaction history', function () {
    $admin  = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $client = ClientFactory::new()->create();
    TransactionFactory::new()->count(3)->create(['client_id' => $client->id]);

    actingAs($admin);

    getJson(route('api.clients.show', $client))
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'transactions' => [
                    '*' => ['id', 'external_id', 'status', 'amount', 'gateway', 'products', 'created_at'],
                ],
            ],
        ]);
});

test('unauthenticated request cannot list clients', function () {
    getJson(route('api.clients.index'))->assertUnauthorized();
});
