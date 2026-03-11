<?php

declare(strict_types = 1);

use App\Models\{Client, Gateway, Transaction};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

use function PHPUnit\Framework\{assertInstanceOf, assertTrue};

test('create transaction model', function () {
    assertTrue(class_exists(Transaction::class));
});

test('relationship with transactions', function () {
    $relationship = (new Transaction())->client();
    assertInstanceOf(BelongsTo::class, $relationship, 'transaction->client()');

    $relationship = (new Transaction())->gateway();
    assertInstanceOf(BelongsTo::class, $relationship, 'transaction->gateway()');

    $relationship = (new Transaction())->products();
    assertInstanceOf(BelongsToMany::class, $relationship, 'transaction->products()');

    $relationship = (new Client())->transactions();
    assertInstanceOf(HasMany::class, $relationship, 'client->transactions()');

    $relationship = (new Gateway())->transactions();
    assertInstanceOf(HasMany::class, $relationship, 'gateway->transactions()');
});
