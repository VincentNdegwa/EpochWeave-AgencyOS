<?php

namespace App\Http\Requests\Imports;

use Illuminate\Foundation\Http\FormRequest;

class ImportAccountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accounts' => ['required', 'array', 'min:1'],
            'accounts.*' => ['required', 'array'],
            'accounts.*.company_name' => ['required', 'string', 'max:255'],
            'accounts.*.phone' => ['nullable', 'string', 'max:50'],
            'accounts.*.website' => ['nullable', 'url', 'max:255'],
            'accounts.*.contact_first_name' => ['nullable', 'string', 'max:100'],
            'accounts.*.contact_last_name' => ['nullable', 'string', 'max:100'],
            'accounts.*.contact_email' => ['nullable', 'email', 'max:255'],
            'accounts.*.contact_phone' => ['nullable', 'string', 'max:50'],
            'accounts.*.status' => ['required', 'string', 'in:lead,opportunity,client,archived'],
            'accounts.*.industry_id' => ['nullable', 'integer'],
            'accounts.*.lead_source_id' => ['nullable', 'integer'],
            'accounts.*.company_size_id' => ['nullable', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $accounts = $this->input('accounts', []);

        if (! is_array($accounts)) {
            return;
        }

        foreach ($accounts as $key => $account) {
            if (! is_array($account)) {
                continue;
            }

            foreach (['industry_id', 'lead_source_id', 'company_size_id'] as $field) {
                if (isset($account[$field]) && ($account[$field] === 'none' || $account[$field] === '')) {
                    $accounts[$key][$field] = null;
                }
            }
        }

        $this->merge(['accounts' => $accounts]);
    }
}
