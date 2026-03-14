<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use Database\Factories\{TransactionFactory, UserFactory};

use function Pest\Laravel\{actingAs, getJson};

test('all authenticated users can list transactions', function () {
    $admin   = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $manager = UserFactory::new()->create(['role' => UserRole::MANAGER->value]);
    $finance = UserFactory::new()->create(['role' => UserRole::FINANCE->value]);
    $user    = UserFactory::new()->create(['role' => UserRole::USER->value]);

    TransactionFactory::new()->count(2)->create();

    collect([$admin, $manager, $finance, $user])->each(function ($agent) {
        actingAs($agent);
        getJson(route('api.transactions.index'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'external_id', 'status', 'amount', 'card_last_numbers', 'gateway', 'products', 'created_at'],
                ],
            ]);
    });
});

test('all authenticated users can view transaction detail', function () {
    $admin   = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $manager = UserFactory::new()->create(['role' => UserRole::MANAGER->value]);
    $finance = UserFactory::new()->create(['role' => UserRole::FINANCE->value]);
    $user    = UserFactory::new()->create(['role' => UserRole::USER->value]);

    $transaction = TransactionFactory::new()->create();

    collect([$admin, $manager, $finance, $user])->each(function ($agent) use ($transaction) {
        actingAs($agent);
        getJson(route('api.transactions.show', $transaction))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => ['id', 'external_id', 'status', 'amount', 'card_last_numbers', 'gateway', 'products', 'created_at'],
            ]);
    });
});

test('transaction detail includes products and gateway', function () {
    $admin       = UserFactory::new()->create(['role' => UserRole::ADMIN->value]);
    $transaction = TransactionFactory::new()->create();

    actingAs($admin);

    getJson(route('api.transactions.show', $transaction))
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'external_id',
                'status',
                'amount',
                'card_last_numbers',
                'gateway'  => ['id', 'name'],
                'products' => [],
                'created_at',
            ],
        ]);
});

test('unauthenticated request cannot list transactions', function () {
    getJson(route('api.transactions.index'))->assertUnauthorized();
});
