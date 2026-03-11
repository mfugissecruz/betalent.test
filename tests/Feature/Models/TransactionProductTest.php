<?php

declare(strict_types = 1);

use App\Models\{Product, Transaction, TransactionProduct};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function PHPUnit\Framework\{assertInstanceOf, assertTrue};

test('create transaction product model', function () {
    assertTrue(class_exists(TransactionProduct::class));
});

test('relationship with transaction and product', function () {
    $relationship = (new TransactionProduct())->transaction();
    assertInstanceOf(BelongsTo::class, $relationship, 'transactionProduct->transaction()');

    $relationship = (new TransactionProduct())->product();
    assertInstanceOf(BelongsTo::class, $relationship, 'transactionProduct->product()');
});
