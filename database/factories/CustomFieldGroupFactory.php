<?php

namespace Database\Factories;

use App\Models\CustomFieldGroup;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomFieldGroup>
 */
class CustomFieldGroupFactory extends Factory
{
    protected $model = CustomFieldGroup::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name' => fake()->words(2, true),
            'applies_to' => fake()->randomElement(['App\Models\Account', 'App\Models\AccountContact']),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
