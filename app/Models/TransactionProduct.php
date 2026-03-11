<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\{BelongsTo, Pivot};

/**
 * @property int $transaction_id
 * @property int $product_id
 * @property int $quantity
 */
class TransactionProduct extends Pivot
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'transaction_id' => 'integer',
            'product_id'     => 'integer',
            'quantity'       => 'integer',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
