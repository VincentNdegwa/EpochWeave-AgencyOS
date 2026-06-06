<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountContact;
use App\Models\ClientProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountContact>
 */
class AccountContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'client_profile_id' => ClientProfile::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'phone' => fake()->optional()->phoneNumber(),
            'job_title' => fake()->optional()->jobTitle(),
            'is_verified' => fake()->boolean(30),
            'is_primary' => fake()->boolean(20),
            'receives_billing' => fake()->boolean(80),
        ];
    }
}
