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
            'website' => fake()->optional()->url(),
            'status' => fake()->randomElement(AccountStatus::cases())->value,
            'token' => Str::random(32),
            'lifetime_value' => fake()->randomNumber(6),
        ];
    }
}
