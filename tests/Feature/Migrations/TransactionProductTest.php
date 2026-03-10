<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('transaction_products migration exits', fn () => expect(Schema::hasTable('transaction_products'))->toBeTrue());

test('transaction_products migration has required columns', function () {
    expect(Schema::hasColumns('transaction_products', [
        'id',
        'transaction_id',
        'product_id',
        'quantity',
    ]))->toBeTrue();
});

test('create foreign key and index to transaction_id column', function () {
    $foreignKeys = collect(Schema::getForeignKeys('transaction_products'));

    expect($foreignKeys->pluck('columns')->flatten())->toContain('transaction_id');
});

test('create foreign key and index to product_id column', function () {
    $foreignKeys = collect(Schema::getForeignKeys('transaction_products'));

    expect($foreignKeys->pluck('columns')->flatten())->toContain('product_id');
});

test('quantity column is integer on transaction_products table', function () {
    $columns = Schema::getColumns('transaction_products');
    $role = collect($columns)->firstWhere('name', 'quantity');

    expect($role['type'])->toBe('integer');
});
