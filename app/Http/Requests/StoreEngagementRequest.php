<?php

namespace App\Http\Requests;

use App\Enums\EngagementDirection;
use App\Enums\EngagementOutcome;
use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use Illuminate\Foundation\Http\FormRequest;

class StoreEngagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['required', 'exists:accounts,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(EngagementType::cases(), 'value'))],
            'direction' => ['required', 'string', 'in:'.implode(',', array_column(EngagementDirection::cases(), 'value'))],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(EngagementStatus::cases(), 'value'))],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'proposal_id' => ['nullable', 'integer', 'exists:proposals,id'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'scheduled_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'follow_up_at' => ['nullable', 'date'],
            'outcome' => ['nullable', 'string', 'in:'.implode(',', array_column(EngagementOutcome::cases(), 'value'))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'proposal_id' => $this->noneToNull($this->input('proposal_id')),
            'invoice_id' => $this->noneToNull($this->input('invoice_id')),
            'project_id' => $this->noneToNull($this->input('project_id')),
            'outcome' => $this->noneToNull($this->input('outcome')),
        ]);
    }

    private function noneToNull(mixed $value): mixed
    {
        return $value === 'none' || $value === '' ? null : $value;
    }
}
