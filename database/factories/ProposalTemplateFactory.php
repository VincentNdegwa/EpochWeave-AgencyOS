<?php

namespace Database\Factories;

use App\Models\ProposalTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProposalTemplate>
 */
class ProposalTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'workspace_id' => \App\Models\Workspace::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'thumbnail_url' => null,
            'content' => [
                'version' => '1.0',
                'blocks' => [],
            ],
            'is_default' => false,
        ];
    }
}
