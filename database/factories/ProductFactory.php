<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $billingType = fake()->randomElement(['one_time', 'recurring']);

        return [
            'workspace_id' => Workspace::factory(),
            'unit_id' => ProductUnit::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'sku' => fake()->bothify('???-###'),
            'unit_price' => fake()->randomNumber(4),
            'billing_type' => $billingType,
            'billing_frequency' => $billingType === 'one_time'
                ? 'none'
                : fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'is_active' => true,
        ];
    }
}
