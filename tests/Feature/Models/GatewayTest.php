<?php

declare(strict_types = 1);

use App\Models\{Gateway, Transaction};
use Illuminate\Database\Eloquent\Relations\HasMany;

use function PHPUnit\Framework\{assertInstanceOf, assertTrue};

test('create gateway model', function () {
    assertTrue(class_exists(Gateway::class));
});

test('relationship with transactions', function () {
    $relationship = (new Gateway())->transactions();
    assertInstanceOf(HasMany::class, $relationship, 'gateway->transactions()');

    $relationship = (new Transaction())->gateway();
    assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship, 'transaction->gateway()');
});
