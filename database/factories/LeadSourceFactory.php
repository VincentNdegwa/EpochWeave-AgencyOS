<?php

namespace Database\Factories;

use App\Models\LeadSource;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadSource>
 */
class LeadSourceFactory extends Factory
{
    protected $model = LeadSource::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name' => fake()->unique()->word(),
            'color' => fake()->optional()->hexColor(),
            'description' => fake()->optional()->sentence(),
            'category' => fake()->optional()->randomElement(['paid', 'organic', 'referral', 'event', 'outbound']),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_default' => fake()->boolean(10),
        ];
    }
}
