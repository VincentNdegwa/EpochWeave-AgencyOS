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
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['nullable', 'string', 'in:lead,opportunity,client,archived'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
}
