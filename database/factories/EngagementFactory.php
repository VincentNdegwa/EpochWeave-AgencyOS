<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Engagement;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Engagement>
 */
class EngagementFactory extends Factory
{
    protected $model = Engagement::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['call', 'email', 'linkedin', 'sms', 'meeting', 'note']),
            'direction' => fake()->randomElement(['outbound', 'inbound']),
            'status' => fake()->randomElement(['planned', 'completed', 'no_answer', 'replied', 'bounced']),
            'subject' => fake()->optional()->sentence(),
            'content' => fake()->optional()->paragraph(),
            'scheduled_at' => fake()->optional()->dateTime(),
            'completed_at' => fake()->optional()->dateTime(),
            'follow_up_at' => fake()->optional()->dateTime(),
            'outcome' => fake()->optional()->randomElement(['positive', 'neutral', 'negative', 'voicemail']),
        ];
    }
}
