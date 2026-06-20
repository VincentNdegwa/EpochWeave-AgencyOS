<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'invoice_id' => Invoice::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(100, 10000),
            'method' => fake()->randomElement(['cash', 'credit_card', 'bank_transfer', 'check']),
            'paid_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'reference' => fake()->optional()->word(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
