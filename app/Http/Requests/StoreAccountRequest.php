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
            'website' => ['nullable', 'url', 'max:255'],
            'lifetime_value' => ['nullable', 'integer', 'min:0'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.first_name' => ['required', 'string', 'max:100'],
            'contacts.*.last_name' => ['required', 'string', 'max:100'],
            'contacts.*.email' => ['required', 'email', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'contacts.*.job_title' => ['nullable', 'string', 'max:150'],
            'contacts.*.is_primary' => ['nullable', 'boolean'],
            'contacts.*.receives_billing' => ['nullable', 'boolean'],
        ];
    }
}
