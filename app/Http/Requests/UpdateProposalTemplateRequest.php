<?php

namespace App\Http\Requests;

use App\Enums\BlockType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateProposalTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'content' => 'nullable|array',
            'content.*.id' => 'required|string',
            'content.*.type' => [
                'required',
                'string',
                Rule::in(BlockType::values()),
            ],
            'content.*.sort_order' => 'required|integer|min:0',
            'content.*.is_locked' => 'required|boolean',
            'content.*.data' => 'required|array',
            'content.*.meta' => 'required|array',
            'content.*.meta.padding_top' => [
                'required',
                'string',
                Rule::in(['none', 'sm', 'md', 'lg', 'xl']),
            ],
            'content.*.meta.padding_bottom' => [
                'required',
                'string',
                Rule::in(['none', 'sm', 'md', 'lg', 'xl']),
            ],
            'content.*.meta.background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'content.*.meta.border_top' => 'required|boolean',
            'content.*.meta.border_bottom' => 'required|boolean',
            'content.*.meta.is_hidden' => 'required|boolean',
            'content.*.meta.notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Template name is required.',
            'name.max' => 'Template name must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'thumbnail_url.url' => 'Thumbnail URL must be a valid URL.',
            'thumbnail_url.max' => 'Thumbnail URL must not exceed 500 characters.',
            'content.array' => 'Content must be an array.',
            'content.*.id.required' => 'Each content block must have an ID.',
            'content.*.type.required' => 'Each content block must have a type.',
            'content.*.type.in' => 'Invalid block type.',
            'content.*.sort_order.required' => 'Each content block must have a sort order.',
            'content.*.sort_order.min' => 'Sort order must be 0 or greater.',
            'content.*.is_locked.required' => 'Each content block must have a lock status.',
            'content.*.data.required' => 'Each content block must have data.',
            'content.*.meta.required' => 'Each content block must have meta data.',
            'content.*.meta.padding_top.required' => 'Each content block must have top padding.',
            'content.*.meta.padding_top.in' => 'Invalid padding size.',
            'content.*.meta.padding_bottom.required' => 'Each content block must have bottom padding.',
            'content.*.meta.padding_bottom.in' => 'Invalid padding size.',
            'content.*.meta.background_color.regex' => 'Background color must be a valid hex color.',
            'content.*.meta.border_top.required' => 'Each content block must have top border setting.',
            'content.*.meta.border_bottom.required' => 'Each content block must have bottom border setting.',
            'content.*.meta.is_hidden.required' => 'Each content block must have hidden status.',
        ];
    }
}
