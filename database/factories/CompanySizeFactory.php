<?php

namespace Database\Factories;

use App\Models\CompanySize;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanySize>
 */
class CompanySizeFactory extends Factory
{
    protected $model = CompanySize::class;

    public function definition(): array
    {
        $min = fake()->randomElement([1, 11, 51, 201, 501, 1001, 5001, 10001]);
        $max = $min === 10001 ? null : $min + fake()->randomElement([9, 49, 199, 499, 999, 4999, 9999]);

        return [
            'workspace_id' => Workspace::factory(),
            'label' => fake()->unique()->word(),
            'min_employees' => $min,
            'max_employees' => $max,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_default' => fake()->boolean(10),
        ];
    }
}
