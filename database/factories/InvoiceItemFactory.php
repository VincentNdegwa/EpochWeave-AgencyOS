<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $discountType = fake()->randomElement(['percentage', 'fixed']);
        $taxType = fake()->randomElement(['percentage', 'fixed']);

        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => Product::factory(),
            'item_name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'unit_label' => fake()->randomElement(['Pcs', 'hrs', 'mo', 'seat']),
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit_price' => fake()->numberBetween(500, 10_000),
            'subtotal' => fake()->numberBetween(500, 10_000),
            'discount_type' => $discountType,
            'discount_value' => $discountType === 'percentage'
                ? fake()->randomFloat(2, 5, 25)
                : fake()->randomFloat(2, 10, 500),
            'discount_amount' => 0,
            'tax_type' => $taxType,
            'tax_value' => $taxType === 'percentage'
                ? fake()->randomFloat(2, 5, 25)
                : fake()->randomFloat(2, 10, 500),
            'total_tax_amount' => 0,
            'total' => fake()->numberBetween(500, 10_000),
            'position' => 0,
        ];
    }
}
