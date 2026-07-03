<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
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
            'account_id' => Account::factory(),
            'account_contact_id' => null,
            'created_by' => User::factory(),
            'user_id' => null,
            'title' => fake()->sentence(4),
            'proposal_number' => 'PROP-'.fake()->unique()->numberBetween(1000, 9999),
            'proposal_status_id' => null,
            'valid_until' => fake()->date(),
            'currency' => fake()->randomElement(['USD', 'KES', 'NGN', 'GHS']),
            'content' => json_encode([]),
            'subtotal' => fake()->randomFloat(2, 1000, 50000),
            'discount_total' => 0,
            'total_tax_amount' => 0,
            'grand_total' => fake()->randomFloat(2, 1000, 50000),
            'requires_deposit' => false,
            'deposit_type' => null,
            'deposit_value' => null,
            'deposit_amount' => null,
            'token' => fake()->uuid(),
            'password_hash' => null,
            'sent_at' => null,
            'viewed_at' => null,
            'last_viewed_at' => null,
            'view_count' => 0,
            'decided_at' => null,
            'accepted_at' => null,
            'signed_at' => null,
            'expired_at' => null,
        ];
    }
}
