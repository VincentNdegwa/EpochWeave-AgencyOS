<?php

namespace App\Services;

use App\Models\WorkspaceSetting;
use Illuminate\Support\Facades\Auth;

class UserPreferenceService
{
    public function getDisplayMode(int $workspaceId, int $userId): string
    {
        $setting = WorkspaceSetting::where('workspace_id', $workspaceId)
            ->where('submodule', 'proposals_display')
            ->first();

        if ($setting && isset($setting->settings['display_mode'])) {
            return $setting->settings['display_mode'];
        }

        return 'list';
    }

    public function setDisplayMode(int $workspaceId, int $userId, string $displayMode): void
    {
        WorkspaceSetting::updateOrCreate(
            [
                'workspace_id' => $workspaceId,
                'submodule' => 'proposals_display',
            ],
            [
                'settings' => [
                    'display_mode' => $displayMode,
                ],
            ]
        );
    }

    public function getUserPreferences(int $workspaceId, int $userId): array
    {
        $setting = WorkspaceSetting::where('workspace_id', $workspaceId)
            ->where('submodule', 'proposals_display')
            ->first();

        return $setting ? $setting->settings : [];
    }
}
