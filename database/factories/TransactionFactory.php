<?php

declare(strict_types = 1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id'         => ClientFactory::new(),
            'gateway_id'        => GatewayFactory::new(),
            'external_id'       => fake()->uuid(),
            'status'            => fake()->randomElement(['pending', 'paid', 'failed', 'charged_back']),
            'amount'            => fake()->numberBetween(1, 1000),
            'card_last_numbers' => fake()->numerify('####'),
        ];
    }
}
