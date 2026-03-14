<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany};

/**
 * @property int $id
 * @property string $name
 * @property int $amount
*/
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
    ];

    public static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->amount = (int) round($product->amount * 100);
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('amount')) {
                $product->amount = (int) round($product->amount * 100);
            }
        });
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class)
            ->withPivot('quantity')
            ->using(TransactionProduct::class);
    }

}
