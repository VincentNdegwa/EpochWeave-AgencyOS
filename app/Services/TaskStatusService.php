<?php

namespace App\Services;

use App\Models\TaskStatus;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class TaskStatusService
{
    public function listForWorkspace(int $workspaceId): Collection
    {
        return TaskStatus::query()
            ->where('workspace_id', $workspaceId)
            ->orderBy('position')
            ->get();
    }

    public function createStatus(array $data): TaskStatus
    {
        if (! isset($data['position'])) {
            $maxPosition = TaskStatus::where('workspace_id', $data['workspace_id'])->max('position') ?? 0;
            $data['position'] = $maxPosition + 1;
        }

        return TaskStatus::create($data);
    }

    public function updateStatus(TaskStatus $status, array $data): TaskStatus
    {
        $status->update($data);

        return $status->fresh();
    }

    public function deleteStatus(TaskStatus $status): void
    {
        if ($status->is_system) {
            throw new Exception('Cannot delete system task statuses.');
        }

        $status->delete();
    }
}
