<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomFieldGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'applies_to' => ['required', 'string', 'in:App\Models\Account,App\Models\AccountContact'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:255'],
        ];
    }
}
