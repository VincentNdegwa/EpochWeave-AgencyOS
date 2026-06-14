<?php

namespace App\Http\Requests;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'color' => ['sometimes', 'nullable', 'regex:/^#([0-9a-f]{6})$/i'],
            'position' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_default' => ['sometimes', 'nullable', 'boolean'],
            'is_closed' => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}
