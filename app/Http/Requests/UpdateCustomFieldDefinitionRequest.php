<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomFieldDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
