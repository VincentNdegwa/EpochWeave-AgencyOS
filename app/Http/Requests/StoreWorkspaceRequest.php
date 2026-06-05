<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('workspaces')],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency' => ['nullable', 'string', 'max:10'],
            'white_label' => ['boolean'],
            'domain' => ['nullable', 'string', 'max:255'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'primary_color' => ['nullable', 'string', 'max:7'],
        ];
    }
}
