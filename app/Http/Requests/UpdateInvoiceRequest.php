<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $invoiceId = $this->route('invoice');

        return [
            'account_id' => 'sometimes|required|exists:accounts,id',
            'account_contact_id' => 'nullable|exists:account_contacts,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'project_id' => 'nullable|exists:projects,id',
            'user_id' => 'nullable|exists:users,id',
            'invoice_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('invoices', 'invoice_number')->ignore($invoiceId),
            ],
            'status' => 'nullable|string|in:draft,sent,paid,void,overdue',
            'currency' => 'nullable|string|size:3',
            'issue_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'line_items' => 'sometimes|array',
            'line_items.*.id' => 'required|string',
            'line_items.*.item_name' => 'required|string|max:255',
            'line_items.*.description' => 'nullable|string',
            'line_items.*.unit_label' => 'required|string|max:50',
            'line_items.*.quantity' => 'required|numeric|min:0',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.subtotal' => 'required|numeric|min:0',
            'line_items.*.discount_type' => 'nullable|in:none,percentage,fixed',
            'line_items.*.discount_value' => 'nullable|numeric|min:0',
            'line_items.*.tax_type' => 'nullable|in:none,percentage,fixed',
            'line_items.*.tax_value' => 'nullable|numeric|min:0',
        ];
    }
}
