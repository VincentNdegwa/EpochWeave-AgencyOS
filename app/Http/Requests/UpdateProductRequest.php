<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['sometimes', 'required', 'exists:product_units,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:100'],
            'unit_price' => ['sometimes', 'required', 'integer', 'min:0'],
            'billing_type' => ['sometimes', 'required', 'string', 'in:one_time,recurring'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
