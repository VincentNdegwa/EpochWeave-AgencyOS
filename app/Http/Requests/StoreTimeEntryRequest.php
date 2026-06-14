<?php

namespace App\Http\Requests;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after_or_equal:started_at'],
            'is_billable' => ['nullable', 'boolean'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ];
    }
}
