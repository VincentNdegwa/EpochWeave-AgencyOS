<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'title' => 'required|string|max:255',
            'proposal_number' => 'nullable|string|max:255',
            'currency' => 'required|string|size:3',
            'valid_until' => 'nullable|date|after:now',
            'blocks' => 'required|array|min:1',
            'blocks.*.id' => 'required|string',
            'blocks.*.type' => [
                'required',
                'string',
                Rule::in([
                    'cover',
                    'rich_text',
                    'image',
                    'logo',
                    'divider',
                    'spacer',
                    'pricing_table',
                    'timeline',
                    'team_member',
                    'testimonial',
                    'terms',
                    'signature',
                    'cta',
                    'video_embed',
                    'file_attachment',
                ]),
            ],
            'blocks.*.sort_order' => 'required|integer|min:0',
            'blocks.*.is_locked' => 'required|boolean',
            'blocks.*.data' => 'required|array',
            'blocks.*.meta' => 'required|array',
            'blocks.*.meta.padding_top' => [
                'required',
                'string',
                Rule::in(['none', 'sm', 'md', 'lg', 'xl']),
            ],
            'blocks.*.meta.padding_bottom' => [
                'required',
                'string',
                Rule::in(['none', 'sm', 'md', 'lg', 'xl']),
            ],
            'blocks.*.meta.background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'blocks.*.meta.border_top' => 'required|boolean',
            'blocks.*.meta.border_bottom' => 'required|boolean',
            'blocks.*.meta.is_hidden' => 'required|boolean',
            'blocks.*.meta.notes' => 'nullable|string',
        ];
    }
}
