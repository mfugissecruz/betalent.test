<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('clients migration exits', fn () => expect(Schema::hasTable('clients'))->toBeTrue());

test('clients migration has required columns', function () {
    expect(Schema::hasColumns('clients', [
        'id',
        'name',
        'email',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('email column is unique on clients table', function () {
    $indexes = Schema::getIndexes('clients');

    $unique_indexes = array_filter($indexes, fn ($index) => $index['unique']);
    $indexed_columns = array_merge(...array_column($unique_indexes, 'columns'));

    expect($indexed_columns)->toContain('email');
});
