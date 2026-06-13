<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceSettings\UpdateWorkspaceInvoiceSettingsRequest;
use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use App\Services\WorkspaceSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
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
        $settings = $this->workspaceSettingService->getOrCreate($workspace->id, WorkspaceSetting::SUBMODULE_INVOICES);

        return Inertia::render('workspace-settings/Invoices', [
            'canUpdateWorkspaceSettings' => $canUpdate,
            'invoiceSettings' => $settings->settings,
        ]);
    }

    public function update(UpdateWorkspaceInvoiceSettingsRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace instanceof Workspace) {
            abort(404);
        }

        $validated = $request->validated();
        $settings = $this->workspaceSettingService->getOrCreate($workspace->id, WorkspaceSetting::SUBMODULE_INVOICES);

        $updatedSettings = [
            'payment_due_days' => (int) $validated['payment_due_days'],
            'default_terms' => $validated['default_terms'] ?? null,
            'numbering' => [
                'format' => $validated['numbering']['format'],
                'prefix' => $validated['numbering']['prefix'],
                'delimiter' => $validated['numbering']['delimiter'],
                'sequence_padding' => (int) $validated['numbering']['sequence_padding'],
                'next_sequence_number' => (int) $validated['numbering']['next_sequence_number'],
            ],
        ];

        $settings->settings = $updatedSettings;
        $settings->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invoice settings updated.')]);

        return to_route('workspace-settings.invoices');
    }
}
