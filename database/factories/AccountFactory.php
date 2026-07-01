<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Account;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'company_name' => fake()->company(),
            'phone' => fake()->optional()->phoneNumber(),
            'website' => fake()->optional()->url(),
            'description' => fake()->optional()->paragraph(),
            'founded_at' => fake()->optional()->date(),
            'status' => fake()->randomElement(AccountStatus::cases())->value,
            'token' => Str::random(32),
            'lifetime_value' => fake()->randomNumber(6),
            'annual_revenue' => fake()->optional()->randomNumber(8),
            'employee_count' => fake()->optional()->numberBetween(1, 10000),
        ];
    }
}
