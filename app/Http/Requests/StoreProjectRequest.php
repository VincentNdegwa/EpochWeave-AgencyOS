<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
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
            'status' => ['nullable', 'string', Rule::in(array_map(fn (ProjectStatus $s) => $s->value, ProjectStatus::cases()))],
            'hourly_rate' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'portal_visible' => ['nullable', 'boolean'],
            'members' => ['nullable', 'array'],
            'members.*.user_id' => ['required', 'exists:users,id'],
            'members.*.role' => ['nullable', 'string', 'max:20'],
            'members.*.hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
