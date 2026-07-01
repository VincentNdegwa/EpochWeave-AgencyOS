<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomFieldValue>
 */
class CustomFieldValueFactory extends Factory
{
    protected $model = CustomFieldValue::class;

    public function definition(): array
    {
        return [
            'custom_field_definition_id' => CustomFieldDefinition::factory(),
            'valueable_type' => Account::class,
            'valueable_id' => Account::factory(),
            'value_text' => fake()->optional()->word(),
        ];
    }
}
