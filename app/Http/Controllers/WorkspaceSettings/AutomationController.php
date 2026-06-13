<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Models\WorkspaceSetting;
use App\Services\WorkspaceSettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AutomationController extends Controller
{
    public function __construct(
        private WorkspaceSettingService $workspaceSettingService
    ) {}

    public function edit(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        $automationSettings = $this->workspaceSettingService->getOrCreate(
            $workspace->id,
            WorkspaceSetting::SUBMODULE_AUTOMATION
        );

        return Inertia::render('workspace-settings/Automation', [
            'automationSettings' => $automationSettings,
        ]);
    }

    public function update(Request $request)
    {
        try {
            $workspace = $request->attributes->get('current_workspace');

            $validated = $request->validate([
                'automation_settings' => 'required|array',
            ]);

            $automationSettings = $this->convertSwitchValuesToBoolean($validated['automation_settings']);

            $this->workspaceSettingService->updateSettings(
                $workspace->id,
                WorkspaceSetting::SUBMODULE_AUTOMATION,
                $automationSettings
            );

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Automation settings updated successfully!']);

            return redirect()->back();
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Failed to update automation settings. Please try again.']);

            return redirect()->back();
        }
    }

    private function convertSwitchValuesToBoolean(array $settings): array
    {
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $settings[$key] = $this->convertSwitchValuesToBoolean($value);
            } else {
                $settings[$key] = isset($value);
            }
        }
        return $settings;
    }
}
