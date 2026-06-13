<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'account_id' => Account::factory(),
            'account_contact_id' => null,
            'proposal_id' => null,
            'project_id' => null,
            'created_by' => User::factory(),
            'user_id' => null,
            'invoice_number' => 'INV'.now()->format('Y').'-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => 'draft',
            'token' => Str::uuid(),
            'currency' => fake()->randomElement(['USD', 'KES', 'NGN', 'GHS']),
            'subtotal' => fake()->numberBetween(1000, 50000),
            'total_tax_amount' => 0,
            'discount_total' => 0,
            'grand_total' => fake()->numberBetween(1000, 50000),
            'amount_paid' => 0,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'notes' => fake()->optional()->paragraph(),
            'sent_at' => null,
            'paid_at' => null,
            'voided_at' => null,
        ];
    }
}
