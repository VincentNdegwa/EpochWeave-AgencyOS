<?php

namespace App\Services;

use App\Models\TaskStatus;
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
        return TaskStatus::create($data);
    }

    public function updateStatus(TaskStatus $status, array $data): TaskStatus
    {
        $status->update($data);

        return $status->fresh();
    }

    public function deleteStatus(TaskStatus $status): void
    {
        $status->delete();
    }
}
