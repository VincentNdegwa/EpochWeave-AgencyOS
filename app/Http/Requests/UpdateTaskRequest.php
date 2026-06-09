<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_status_id' => ['sometimes', 'required', 'exists:task_statuses,id'],
            'parent_id' => ['sometimes', 'nullable', 'exists:tasks,id'],
            'title' => ['sometimes', 'required', 'string', 'max:500'],
            'description' => ['sometimes', 'nullable', 'string'],
            'assignee_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'position' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'is_billable' => ['sometimes', 'boolean'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
