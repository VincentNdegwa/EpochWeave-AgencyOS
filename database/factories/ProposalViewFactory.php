<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\ProposalView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProposalView>
 */
class ProposalViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proposal_id' => Proposal::factory(),
            'viewer_id' => $this->faker->randomNumber(),
            'viewer_type' => Client::class,
            'viewed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'is_revisit' => false,
        ];
    }

    public function revisit(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_revisit' => true,
            'viewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
