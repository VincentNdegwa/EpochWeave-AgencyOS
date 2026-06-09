<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['sometimes', 'required', 'exists:accounts,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'color' => ['sometimes', 'nullable', 'regex:/^#([0-9a-f]{6})$/i'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'on_hold', 'completed', 'archived'])],
            'hourly_rate' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'currency' => ['sometimes', 'required', 'string', 'max:10'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'portal_visible' => ['sometimes', 'boolean'],
            'members' => ['sometimes', 'array'],
            'members.*.user_id' => ['required_with:members', 'exists:users,id'],
            'members.*.role' => ['nullable', 'string', 'max:20'],
            'members.*.hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'statuses' => ['sometimes', 'array'],
            'statuses.*.name' => ['required_with:statuses', 'string', 'max:100'],
            'statuses.*.color' => ['nullable', 'regex:/^#([0-9a-f]{6})$/i'],
            'statuses.*.position' => ['nullable', 'integer', 'min:0'],
            'statuses.*.is_default' => ['nullable', 'boolean'],
            'statuses.*.is_closed' => ['nullable', 'boolean'],
        ];
    }
}
