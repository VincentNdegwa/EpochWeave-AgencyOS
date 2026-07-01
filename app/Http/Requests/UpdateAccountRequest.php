<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'founded_at' => ['nullable', 'date'],
            'lifetime_value' => ['nullable', 'integer', 'min:0'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'employee_count' => ['nullable', 'integer', 'min:0'],
            'industry_id' => ['nullable', 'integer', 'exists:industries,id'],
            'lead_source_id' => ['nullable', 'integer', 'exists:lead_sources,id'],
            'company_size_id' => ['nullable', 'integer', 'exists:company_sizes,id'],
        ];
    }
}
