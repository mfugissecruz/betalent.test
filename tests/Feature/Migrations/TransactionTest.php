<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('transactions migration exits', fn () => expect(Schema::hasTable('transactions'))->toBeTrue());

test('transactions migration has required columns', function () {
    expect(Schema::hasColumns('transactions', [
        'id',
        'client_id',
        'gateway_id',
        'external_id',
        'status',
        'amount',
        'card_last_numbers',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('create foreign key and index to client_id column', function () {
    $foreignKeys = collect(Schema::getForeignKeys('transactions'));

    expect($foreignKeys->pluck('columns')->flatten())->toContain('client_id');
});

test('create foreign key and index to gateway_id column', function () {
    $foreignKeys = collect(Schema::getForeignKeys('transactions'));

    expect($foreignKeys->pluck('columns')->flatten())->toContain('gateway_id');
});

test('amount column is integer on transactions table', function () {
    $columns = Schema::getColumns('transactions');
    $role = collect($columns)->firstWhere('name', 'amount');

    expect($role['type'])->toBe('integer');
});
