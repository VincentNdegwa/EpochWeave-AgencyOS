<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', 'in:linkedin,twitter,facebook,instagram,github,youtube,tiktok,other'],
            'url' => ['required', 'url', 'max:500'],
            'handle' => ['nullable', 'string', 'max:100'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'is_verified' => ['nullable', 'boolean'],
        ];
    }
}
