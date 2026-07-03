<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Models\WorkspaceSetting;
use App\Services\WorkspaceSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        private WorkspaceSettingService $workspaceSettingService
    ) {}

    public function edit(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        $settings = $this->workspaceSettingService->getOrCreate(
            $workspace->id,
            WorkspaceSetting::SUBMODULE_NOTIFICATIONS
        );

        return Inertia::render('workspace-settings/Notifications', [
            'notificationSettings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');

            $validated = $request->validate([
                'notification_settings' => 'required|array',
            ]);

            $notificationSettings = $this->convertSwitchValuesToBoolean($validated['notification_settings']);

            $this->workspaceSettingService->updateSettings(
                $workspace->id,
                WorkspaceSetting::SUBMODULE_NOTIFICATIONS,
                $notificationSettings
            );

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Notification settings updated successfully.']);

            return redirect()->back();
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Failed to update notification settings. Please try again.']);

            return redirect()->back()->withInput();
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
