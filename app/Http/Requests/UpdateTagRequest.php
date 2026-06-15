<?php

namespace App\Http\Requests;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }
}
