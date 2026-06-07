<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Proposal;
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
            'title' => fake()->sentence(4),
            'status' => 'draft',
            'valid_until' => fake()->date(),
            'currency' => fake()->randomElement(['USD', 'KES', 'NGN', 'GHS']),
            'blocks' => [],
            'total_amount' => fake()->randomNumber(5),
            'token' => fake()->uuid(),
            'sent_at' => null,
            'accepted_at' => null,
            'rejected_at' => null,
        ];
    }
}
