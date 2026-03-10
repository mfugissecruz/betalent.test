<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('gateways table exits', fn () => expect(Schema::hasTable('gateways'))->toBeTrue());

test('gateways migration has required columns', function () {
    expect(Schema::hasColumns('gateways', [
        'id',
        'name',
        'is_active',
        'priority',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});
