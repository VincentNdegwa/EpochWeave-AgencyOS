<?php

namespace Database\Factories;

use App\Models\ClientProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientProfile>
 */
class ClientProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->email(),
            'password' => bcrypt('password'),
        ];
    }
}
