<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\SocialProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialProfile>
 */
class SocialProfileFactory extends Factory
{
    protected $model = SocialProfile::class;

    public function definition(): array
    {
        return [
            'profileable_type' => Account::class,
            'profileable_id' => Account::factory(),
            'platform' => fake()->randomElement(['linkedin', 'twitter', 'facebook', 'instagram', 'github', 'youtube']),
            'url' => fake()->url(),
            'handle' => fake()->optional()->word(),
            'followers_count' => fake()->optional()->numberBetween(100, 100000),
            'is_verified' => fake()->boolean(5),
        ];
    }

    public function forAccount(Account $account): self
    {
        return $this->state([
            'profileable_type' => Account::class,
            'profileable_id' => $account->id,
        ]);
    }
}
