<?php

declare(strict_types = 1);

use App\Models\{Client, Transaction};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

use function PHPUnit\Framework\{assertInstanceOf, assertTrue};

test('create client model', function () {
    assertTrue(class_exists(Client::class));
});

test('relationship with transactions', function () {
    $relationship = (new Client())->transactions();
    assertInstanceOf(HasMany::class, $relationship, 'client->transactions()');

    $relationship = (new Transaction())->client();
    assertInstanceOf(BelongsTo::class, $relationship, 'transaction->client()');
});
