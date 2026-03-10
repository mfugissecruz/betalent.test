<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('products migration exits', fn () => expect(Schema::hasTable('products'))->toBeTrue());

test('products migration has required columns', function () {
    expect(Schema::hasColumns('products', [
        'id',
        'name',
        'amount',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('amount column is integer on products table', function () {
    $columns = Schema::getColumns('products');
    $role = collect($columns)->firstWhere('name', 'amount');

    expect($role['type'])->toBe('integer');
});
