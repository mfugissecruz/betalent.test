<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('users migration exits', fn () => expect(Schema::hasTable('users'))->toBeTrue());

test('users migration has required columns', function () {
    expect(Schema::hasColumns('users', [
        'id',
        'name',
        'email',
        'password',
        'role',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('email column is unique on users table', function () {
    $indexes = Schema::getIndexes('users');

    $unique_indexes = array_filter($indexes, fn ($index) => $index['unique']);
    $indexed_columns = array_merge(...array_column($unique_indexes, 'columns'));

    expect($indexed_columns)->toContain('email');
});
