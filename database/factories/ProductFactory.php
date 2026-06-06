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
        return [
            'workspace_id' => Workspace::factory(),
            'unit_id' => ProductUnit::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'sku' => fake()->bothify('???-###'),
            'unit_price' => fake()->randomNumber(4),
            'billing_type' => fake()->randomElement(['one_time', 'recurring']),
            'is_active' => true,
        ];
    }
}
