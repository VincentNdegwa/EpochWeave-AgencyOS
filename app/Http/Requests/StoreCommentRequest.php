<?php

namespace App\Http\Requests;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
            'pinned_at' => ['nullable', 'date'],
        ];
    }
}
