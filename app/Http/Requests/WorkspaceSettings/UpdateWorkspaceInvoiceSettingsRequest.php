<?php

namespace App\Http\Requests\WorkspaceSettings;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceInvoiceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->attributes->get('current_workspace');

        if (! $workspace instanceof Workspace) {
            return false;
        }

        if (! $this->user()) {
            return false;
        }

        if (! $this->user()->rolesTeams()->where('id', $workspace->id)->exists()) {
            return false;
        }

        return $this->user()->hasRole('admin', $workspace);
    }

    public function rules(): array
    {
        return [
            'payment_due_days' => ['required', 'integer', 'min:0', 'max:365'],
            'default_terms' => ['nullable', 'string'],
            'numbering.format' => ['required', 'string', 'max:255'],
            'numbering.prefix' => ['required', 'string', 'max:50'],
            'numbering.delimiter' => ['required', 'string', 'max:10'],
            'numbering.sequence_padding' => ['required', 'integer', 'min:0', 'max:10'],
            'numbering.next_sequence_number' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }
}
