<?php

namespace Database\Factories;

use App\Models\CreditNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditNote>
 */
class CreditNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => 1,
            'invoice_id' => 1,
            'payment_id' => null,
            'user_id' => 1,
            'amount' => fake()->numberBetween(1000, 50000),
            'reason' => fake()->sentence(),
            'refunded_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'reference' => fake()->optional()->uuid(),
        ];
    }
}
