<?php

namespace App\Services;

class UserPreferenceService
{
    public function __construct(private WorkspaceSettingService $workspaceSettingService) {}

    public function getDisplayMode(int $workspaceId, int $userId): string
    {
        $setting = $this->workspaceSettingService->getOrCreate($workspaceId, 'proposals_display');

        return $setting->settings['display_mode'] ?? 'list';
    }

    public function setDisplayMode(int $workspaceId, int $userId, string $displayMode): void
    {
        $this->workspaceSettingService->updateSettings($workspaceId, 'proposals_display', [
            'display_mode' => $displayMode,
        ]);
    }

    public function getUserPreferences(int $workspaceId, int $userId): array
    {
        $setting = $this->workspaceSettingService->getOrCreate($workspaceId, 'proposals_display');

        return $setting->settings ?? [];
    }
}
