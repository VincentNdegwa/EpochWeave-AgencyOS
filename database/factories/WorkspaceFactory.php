<?php

namespace Database\Factories;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workspace>
 */
class WorkspaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->slug(),
            'display_name' => fake()->company(),
            'description' => fake()->sentence(),
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP', 'JPY']),
            'white_label' => fake()->boolean(),
            'domain' => fake()->optional()->domainName(),
            'logo_url' => fake()->optional()->imageUrl(),
            'primary_color' => fake()->optional()->hexColor(),
        ];
    }
}
