<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['required', 'exists:accounts,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'regex:/^#([0-9a-f]{6})$/i'],
            'status' => ['nullable', 'string', Rule::in(['active', 'on_hold', 'completed', 'archived'])],
            'hourly_rate' => ['nullable', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'portal_visible' => ['nullable', 'boolean'],
            'members' => ['nullable', 'array'],
            'members.*.user_id' => ['required', 'exists:users,id'],
            'members.*.role' => ['nullable', 'string', 'max:20'],
            'members.*.hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'statuses' => ['nullable', 'array'],
            'statuses.*.name' => ['required_with:statuses', 'string', 'max:100'],
            'statuses.*.color' => ['nullable', 'regex:/^#([0-9a-f]{6})$/i'],
            'statuses.*.position' => ['nullable', 'integer', 'min:0'],
            'statuses.*.is_default' => ['nullable', 'boolean'],
            'statuses.*.is_closed' => ['nullable', 'boolean'],
        ];
    }
}
