<?php

namespace App\Http\Requests;

class StoreTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'regex:/^#([0-9a-f]{6})$/i'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'is_closed' => ['nullable', 'boolean'],
        ];
    }
}
