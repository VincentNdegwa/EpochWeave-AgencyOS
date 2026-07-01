<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'addressable_type' => Account::class,
            'addressable_id' => Account::factory(),
            'type' => fake()->randomElement(['billing', 'shipping', 'primary', 'office']),
            'label' => fake()->optional()->word(),
            'street_1' => fake()->streetAddress(),
            'street_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->optional()->state(),
            'postal_code' => fake()->optional()->postcode(),
            'country' => fake()->country(),
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
            'is_primary' => fake()->boolean(20),
        ];
    }

    public function forAccount(Account $account): self
    {
        return $this->state([
            'addressable_type' => Account::class,
            'addressable_id' => $account->id,
        ]);
    }

    public function billing(): self
    {
        return $this->state(['type' => 'billing']);
    }

    public function shipping(): self
    {
        return $this->state(['type' => 'shipping']);
    }

    public function primary(): self
    {
        return $this->state(['is_primary' => true]);
    }
}
