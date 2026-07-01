<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profileable_type' => ['required', 'string', 'in:App\Models\Account,App\Models\AccountContact'],
            'profileable_id' => ['required', 'integer'],
            'platform' => ['required', 'string', 'in:linkedin,twitter,facebook,instagram,github,youtube,tiktok,other'],
            'url' => ['required', 'url', 'max:500'],
            'handle' => ['nullable', 'string', 'max:100'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'is_verified' => ['nullable', 'boolean'],
        ];
    }
}
