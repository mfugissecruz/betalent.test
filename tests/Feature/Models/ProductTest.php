<?php

declare(strict_types = 1);

use App\Models\{Product, Transaction};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use function PHPUnit\Framework\{assertInstanceOf, assertTrue};

test('create product model', function () {
    assertTrue(class_exists(Product::class));
});

test('relationship with transactions', function () {
    $relationship = (new Product())->transactions();
    assertInstanceOf(BelongsToMany::class, $relationship, 'product->transactions()');

    $relationship = (new Transaction())->products();
    assertInstanceOf(BelongsToMany::class, $relationship, 'transaction->products()');
});
