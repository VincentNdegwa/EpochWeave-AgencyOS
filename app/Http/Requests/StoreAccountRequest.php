<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
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
            'contacts' => ['nullable', 'array'],
            'contacts.*.first_name' => ['required', 'string', 'max:100'],
            'contacts.*.last_name' => ['required', 'string', 'max:100'],
            'contacts.*.email' => ['required', 'email', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'contacts.*.job_title' => ['nullable', 'string', 'max:150'],
            'contacts.*.date_of_birth' => ['nullable', 'date'],
            'contacts.*.department' => ['nullable', 'string', 'max:100'],
            'contacts.*.preferred_contact_method' => ['nullable', 'string', 'in:email,phone'],
            'contacts.*.notes' => ['nullable', 'string', 'max:2000'],
            'contacts.*.is_primary' => ['nullable', 'boolean'],
            'contacts.*.receives_billing' => ['nullable', 'boolean'],
        ];
    }
}
