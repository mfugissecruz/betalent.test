<?php

declare(strict_types = 1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gateway>
 */
class GatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => fake()->word(),
            'is_active' => true,
            'priority'  => fake()->unique()->numberBetween(1, 10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
