<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomFieldDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'custom_field_group_id' => ['required', 'integer', 'exists:custom_field_groups,id'],
            'label' => ['required', 'string', 'max:100'],
            'field_type' => ['required', 'string', 'in:text,textarea,number,boolean,date,select,multiselect'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:100'],
            'validation_rules' => ['nullable', 'array'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:255'],
        ];
    }
}
