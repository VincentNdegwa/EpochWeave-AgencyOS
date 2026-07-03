<?php

namespace App\Services;

use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use Illuminate\Support\Arr;

class WorkspaceSettingService
{
    public function getOrCreate(int $workspaceId, string $submodule): WorkspaceSetting
    {
        $defaults = $this->getDefaultSettings($submodule);

        return WorkspaceSetting::firstOrCreate(
            [
                'workspace_id' => $workspaceId,
                'submodule' => $submodule,
            ],
            [
                'settings' => $defaults,
            ]
        );
    }

    public function ensureDefaults(Workspace $workspace): void
    {
        foreach (array_keys(config('workspace-settings.defaults', [])) as $submodule) {
            $this->getOrCreate($workspace->id, $submodule);
        }
    }

    public function updateSettings(int $workspaceId, string $submodule, array $settings): WorkspaceSetting
    {
        $setting = $this->getOrCreate($workspaceId, $submodule);

        $setting->settings = $settings;
        $setting->save();

        return $setting->refresh();
    }

    public function incrementNumberingSequence(WorkspaceSetting $setting, int $currentSequence, string $path = 'numbering.next_sequence_number'): WorkspaceSetting
    {
        $settings = $setting->settings ?? [];
        Arr::set($settings, $path, $currentSequence + 1);

        $setting->settings = $settings;
        $setting->save();

        return $setting->refresh();
    }

    private function getDefaultSettings(string $submodule): array
    {
        return config("workspace-settings.defaults.{$submodule}", []);
    }
}
