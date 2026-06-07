<?php

namespace App\Http\Requests\WorkspaceSettings;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkspaceGeneralSettingsRequest extends FormRequest
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
        $workspace = $this->attributes->get('current_workspace');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('workspaces', 'name')->ignore($workspace instanceof Workspace ? $workspace->id : null),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency' => ['nullable', 'string', 'max:10'],
            'white_label' => ['required', 'boolean'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['nullable', 'string', 'max:7'],
        ];
    }
}
