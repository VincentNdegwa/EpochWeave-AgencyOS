<?php

namespace App\Http\Requests;

use App\Enums\BlockType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateProposalRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'currency' => 'sometimes|required|string|size:3',
            'valid_until' => 'nullable|date|after:now',
            'proposal_status_id' => 'sometimes|required|exists:proposal_statuses,id',
            'account_contact_id' => 'sometimes|nullable|exists:account_contacts,id',
            'user_id' => 'sometimes|nullable|exists:users,id',
            'blocks' => 'sometimes|array',
            'blocks.*.id' => 'required|string',
            'blocks.*.type' => [
                'required',
                'string',
                Rule::in(BlockType::values()),
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
            'line_items' => 'sometimes|array',
            'line_items.*.id' => 'required|string',
            'line_items.*.description' => 'required|string|max:255',
            'line_items.*.item_description' => 'nullable|string',
            'line_items.*.unit' => 'required|string|max:50',
            'line_items.*.quantity' => 'required|numeric|min:0',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.subtotal' => 'required|numeric|min:0',
            'line_items.*.billing_type' => 'required|in:one_time,recurring',
            'line_items.*.billing_frequency' => 'required|in:none,monthly,quarterly,yearly',
            'line_items.*.is_optional' => 'required|boolean',
            'line_items.*.product_id' => 'nullable|integer|exists:products,id',
            'line_items.*.item_discount_type' => 'nullable|in:none,percentage,fixed',
            'line_items.*.item_discount_value' => 'nullable|numeric|min:0',
        ];
    }
}
