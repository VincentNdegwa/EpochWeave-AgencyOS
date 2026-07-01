<?php

namespace Database\Factories;

use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomFieldDefinition>
 */
class CustomFieldDefinitionFactory extends Factory
{
    protected $model = CustomFieldDefinition::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['text', 'number', 'boolean', 'date', 'select', 'textarea']);

        return [
            'custom_field_group_id' => CustomFieldGroup::factory(),
            'label' => fake()->words(2, true),
            'field_type' => $type,
            'options' => in_array($type, ['select', 'multiselect']) ? fake()->words(4) : null,
            'validation_rules' => null,
            'is_required' => fake()->boolean(20),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
