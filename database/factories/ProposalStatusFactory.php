<?php

namespace Database\Factories;

use App\Models\ProposalStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProposalStatus>
 */
class ProposalStatusFactory extends Factory
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
            'title' => $this->faker->words(2, true),
            'color' => $this->faker->hexColor(),
            'is_system' => false,
            'automation_trigger' => null,
            'position' => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * Create a system status.
     */
    public function system(string $trigger, string $title, string $color, int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
            'color' => $color,
            'is_system' => true,
            'automation_trigger' => $trigger,
            'position' => $position,
        ]);
    }
}
