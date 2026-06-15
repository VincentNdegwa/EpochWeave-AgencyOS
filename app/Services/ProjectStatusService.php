<?php

namespace App\Services;

use App\Models\ProjectStatus;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ProjectStatusService
{
    public function listForWorkspace(int $workspaceId): Collection
    {
        return ProjectStatus::query()
            ->where('workspace_id', $workspaceId)
            ->orderBy('position')
            ->get();
    }

    public function createStatus(array $data): ProjectStatus
    {
        if (! isset($data['position'])) {
            $maxPosition = ProjectStatus::where('workspace_id', $data['workspace_id'])->max('position') ?? 0;
            $data['position'] = $maxPosition + 1;
        }

        return ProjectStatus::create($data);
    }

    public function updateStatus(ProjectStatus $status, array $data): ProjectStatus
    {
        $status->update($data);

        return $status->fresh();
    }

    public function deleteStatus(ProjectStatus $status): void
    {
        if ($status->is_system) {
            throw new Exception('Cannot delete system project statuses.');
        }

        $status->delete();
    }

    public function getStatusByTrigger(int $workspaceId, string $trigger): ?ProjectStatus
    {
        return ProjectStatus::where('workspace_id', $workspaceId)
            ->where('automation_trigger', $trigger)
            ->first();
    }
}
