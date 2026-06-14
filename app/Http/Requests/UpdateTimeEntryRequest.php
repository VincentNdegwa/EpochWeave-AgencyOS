<?php

namespace App\Http\Requests;

class UpdateTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['sometimes', 'required', 'exists:projects,id'],
            'task_id' => ['sometimes', 'nullable', 'exists:tasks,id'],
            'user_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'description' => ['sometimes', 'nullable', 'string'],
            'started_at' => ['sometimes', 'required', 'date'],
            'ended_at' => ['sometimes', 'required', 'date', 'after_or_equal:started_at'],
            'is_billable' => ['sometimes', 'nullable', 'boolean'],
            'hourly_rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'date' => ['sometimes', 'required', 'date'],
        ];
    }
}
