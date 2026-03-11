<?php

declare(strict_types = 1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionProduct>
 */
class TransactionProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => TransactionFactory::new(),
            'product_id'     => ProductFactory::new(),
            'quantity'       => fake()->numberBetween(1, 10),
        ];
    }
}
