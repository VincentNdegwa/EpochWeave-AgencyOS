<?php

namespace App\Http\Requests;

use App\Enums\BlockType;
use App\Enums\BillingType;
use App\Enums\BillingFrequency;
use App\Enums\DiscountType;
use App\Enums\TaxType;
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
            'account_contact_id' => 'nullable|exists:account_contacts,id',
            'title' => 'required|string|max:255',
            'proposal_number' => 'nullable|string|max:255',
            'proposal_status_id' => 'nullable|exists:proposal_statuses,id',
            'user_id' => 'nullable|exists:users,id',
            'template_id' => 'nullable|exists:proposal_templates,id',
            'valid_until' => 'nullable|date|after:now',
            'blocks' => 'nullable|array',
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
            'line_items' => 'nullable|array',
            'line_items.*.id' => 'required|string',
            'line_items.*.description' => 'required|string|max:255',
            'line_items.*.item_description' => 'nullable|string',
            'line_items.*.unit' => 'required|string|max:50',
            'line_items.*.quantity' => 'required|numeric|min:0',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.subtotal' => 'required|numeric|min:0',
            'line_items.*.billing_type' => [
                'required',
                Rule::in(array_column(BillingType::cases(), 'value')),
            ],
            'line_items.*.billing_frequency' => [
                'required',
                Rule::in(array_column(BillingFrequency::cases(), 'value')),
            ],
            'line_items.*.is_optional' => 'required|boolean',
            'line_items.*.product_id' => 'nullable|integer|exists:products,id',
            'line_items.*.discount_type' => [
                'nullable',
                Rule::in(array_column(DiscountType::cases(), 'value')),
            ],
            'line_items.*.discount_value' => 'nullable|numeric|min:0',
            'line_items.*.tax_type' => [
                'nullable',
                Rule::in(array_column(TaxType::cases(), 'value')),
            ],
            'line_items.*.tax_value' => 'nullable|numeric|min:0',
        ];
    }
}
