<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
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
            'status' => ['sometimes', 'required', 'string', Rule::in(array_map(fn (ProjectStatus $s) => $s->value, ProjectStatus::cases()))],
            'hourly_rate' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'portal_visible' => ['sometimes', 'boolean'],
            'members' => ['sometimes', 'array'],
            'members.*.user_id' => ['required_with:members', 'exists:users,id'],
            'members.*.role' => ['nullable', 'string', 'max:20'],
            'members.*.hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
