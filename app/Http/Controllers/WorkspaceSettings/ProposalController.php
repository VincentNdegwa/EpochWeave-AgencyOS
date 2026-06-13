<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceSettings\UpdateWorkspaceProposalSettingsRequest;
use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use App\Services\WorkspaceSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProposalController extends Controller
{
    public function __construct(private WorkspaceSettingService $workspaceSettingService) {}

    public function edit(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace instanceof Workspace) {
            abort(404);
        }

        if (! $request->user()?->rolesTeams()->where('id', $workspace->id)->exists()) {
            abort(403);
        }

        $canUpdate = $request->user()?->hasRole('admin', $workspace) ?? false;
        $settings = $this->workspaceSettingService->getOrCreate($workspace->id, WorkspaceSetting::SUBMODULE_PROPOSALS);

        return Inertia::render('workspace-settings/Proposals', [
            'canUpdateWorkspaceSettings' => $canUpdate,
            'proposalSettings' => $settings->settings,
        ]);
    }

    public function update(UpdateWorkspaceProposalSettingsRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace instanceof Workspace) {
            abort(404);
        }

        $validated = $request->validated();

        $updatedSettings = [
            'default_validity_days' => (int) $validated['default_validity_days'],
            'auto_archive' => (bool) $validated['auto_archive'],
            'default_deposit_percentage' => (int) $validated['default_deposit_percentage'],
            'payment_due_days' => (int) $validated['payment_due_days'],
            'default_terms' => $validated['default_terms'] ?? null,
            'sender_name' => $validated['sender_name'],
            'sender_title' => $validated['sender_title'] ?? null,
            'numbering' => [
                'format' => $validated['numbering']['format'],
                'prefix' => $validated['numbering']['prefix'],
                'delimiter' => $validated['numbering']['delimiter'],
                'sequence_padding' => (int) $validated['numbering']['sequence_padding'],
                'next_sequence_number' => (int) $validated['numbering']['next_sequence_number'],
            ],
        ];

        $this->workspaceSettingService->updateSettings(
            $workspace->id,
            WorkspaceSetting::SUBMODULE_PROPOSALS,
            $updatedSettings,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Proposal settings updated.')]);

        return to_route('workspace-settings.proposals');
    }
}
