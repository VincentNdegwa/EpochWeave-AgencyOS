<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Proposal;
use App\Models\ProposalItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProposalItem>
 */
class ProposalItemFactory extends Factory
{
    protected $model = ProposalItem::class;

    public function definition(): array
    {
        $billingType = fake()->randomElement(['one_time', 'recurring']);

        return [
            'proposal_id' => Proposal::factory(),
            'product_id' => Product::factory(),
            'item_name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'unit_label' => fake()->randomElement(['hrs', 'mo', 'seat']),
            'billing_type' => $billingType,
            'billing_frequency' => $billingType === 'one_time'
                ? 'none'
                : fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit_price' => fake()->numberBetween(500, 10_000),
            'subtotal' => fake()->numberBetween(500, 10_000),
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
            'total' => fake()->numberBetween(500, 10_000),
            'is_optional' => fake()->boolean(20),
            'is_selected' => true,
            'position' => 0,
        ];
    }
}
