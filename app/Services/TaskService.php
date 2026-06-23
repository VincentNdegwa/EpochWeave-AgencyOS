<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TimeEntry;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class TaskService
{
    public function __construct(private ActivityService $activityService) {}

    public function createTask(Project $project, array $data): Task
    {
        try {
            return DB::transaction(function () use ($project, $data) {
                $status = $this->getStatusForProject($project, (int) $data['task_status_id']);

                $payload = [
                    'workspace_id' => $project->workspace_id,
                    'task_status_id' => $status->id,
                    'project_id' => $project->id,
                    'parent_id' => $data['parent_id'] ?? null,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'assignee_id' => $data['assignee_id'] ?? null,
                    'priority' => $data['priority'] ?? 'medium',
                    'position' => $data['position'] ?? $this->nextPosition($project),
                    'start_date' => $data['start_date'] ?? null,
                    'due_date' => $data['due_date'] ?? null,
                    'estimated_hours' => $data['estimated_hours'] ?? null,
                    'is_billable' => $data['is_billable'] ?? true,
                    'created_by' => $data['created_by'] ?? null,
                ];

                $task = Task::create($payload);
                $this->activityService->created($task);

                $this->syncTags($task, $data['tag_ids'] ?? []);
                $this->updateCompletionState($task, $status);
                $this->updateProjectCounters($project);

                return $task->fresh(['status', 'assignee', 'tags']);
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to create task: '.$e->getMessage(), 0, $e);
        }
    }

    public function updateTask(Task $task, array $data): Task
    {
        try {
            return DB::transaction(function () use ($task, $data) {
                $project = $task->project;
                $oldStatus = $task->status;

                if (isset($data['task_status_id'])) {
                    $status = $this->getStatusForProject($project, (int) $data['task_status_id']);
                    $data['task_status_id'] = $status->id;
                } else {
                    $status = $oldStatus;
                }

                $task->update($data);
                $this->activityService->updated($task);

                if (array_key_exists('tag_ids', $data)) {
                    $this->syncTags($task, $data['tag_ids'] ?? []);
                }

                if (isset($status)) {
                    $this->updateCompletionState($task, $status);
                }

                if (isset($oldStatus) && isset($status) && $oldStatus->id !== $status->id) {
                    $this->handleTimeTracking($task, $oldStatus, $status);
                }

                $this->updateProjectCounters($project);

                return $task->fresh(['status', 'assignee', 'tags']);
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to update task: '.$e->getMessage(), 0, $e);
        }
    }

    public function deleteTask(Task $task): void
    {
        try {
            $project = $task->project;
            $this->activityService->deleted($task);
            $task->delete();
            $this->updateProjectCounters($project);
        } catch (Throwable $e) {
            throw new Exception('Failed to delete task: '.$e->getMessage(), 0, $e);
        }
    }

    private function updateProjectCounters(Project $project): void
    {
        $project->update([
            'tasks_total' => $project->tasks()->count(),
            'tasks_completed' => $project->tasks()->whereNotNull('completed_at')->count(),
        ]);
    }

    private function updateCompletionState(Task $task, TaskStatus $status): void
    {
        $shouldBeCompleted = $status->is_closed;

        if ($shouldBeCompleted && ! $task->completed_at) {
            $task->updateQuietly(['completed_at' => now()]);
        }

        if (! $shouldBeCompleted && $task->completed_at) {
            $task->updateQuietly(['completed_at' => null]);
        }
    }

    private function getStatusForProject(Project $project, int $statusId): TaskStatus
    {
        $status = TaskStatus::query()
            ->where('workspace_id', $project->workspace_id)
            ->whereKey($statusId)
            ->first();

        if (! $status) {
            throw new Exception('Invalid task status selected for this workspace.');
        }

        return $status;
    }

    private function syncTags(Task $task, array $tagIds): void
    {
        if (empty($tagIds)) {
            $task->tags()->detach();

            return;
        }

        $validTags = Tag::query()
            ->whereIn('id', $tagIds)
            ->where('workspace_id', $task->workspace_id)
            ->pluck('id')
            ->all();

        $task->tags()->sync($validTags);
    }

    private function nextPosition(Project $project): int
    {
        return (int) ($project->tasks()->max('position') ?? 0) + 1;
    }

    private function handleTimeTracking(Task $task, TaskStatus $oldStatus, TaskStatus $newStatus): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $movingIntoActive = $newStatus->automation_trigger === 'active' && $oldStatus->automation_trigger !== 'active';
        $movingOutOfActive = $oldStatus->automation_trigger === 'active' && $newStatus->automation_trigger !== 'active';

        if ($movingIntoActive) {
            $this->sealOrphanedTimers($userId);
            $this->startTimeEntry($task, $userId);
        }

        if ($movingOutOfActive) {
            $this->sealTaskTimer($task, $userId, $newStatus);
        }
    }

    private function sealOrphanedTimers(int $userId): void
    {
        TimeEntry::where('user_id', $userId)
            ->whereNull('ended_at')
            ->each(function (TimeEntry $entry) {
                $this->sealTimeEntry($entry, 'System closed: User shifted focus to a separate assignment.');
            });
    }

    private function startTimeEntry(Task $task, int $userId): void
    {
        $rate = $task->is_billable && $task->project->hourly_rate
            ? $task->project->hourly_rate
            : 0.00;

        TimeEntry::create([
            'workspace_id' => $task->workspace_id,
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'user_id' => $userId,
            'started_at' => now(),
            'date' => now()->toDateString(),
            'is_billable' => $task->is_billable,
            'hourly_rate' => $rate,
        ]);
    }

    private function sealTaskTimer(Task $task, int $userId, TaskStatus $targetStatus): void
    {
        $runningTimer = TimeEntry::where('task_id', $task->id)
            ->where('user_id', $userId)
            ->whereNull('ended_at')
            ->first();

        if (! $runningTimer) {
            return;
        }

        $note = $targetStatus->automation_trigger === 'completed'
            ? 'Work completed via dashboard execution pipeline.'
            : 'System logged: Task returned to pending status.';

        $this->sealTimeEntry($runningTimer, $note);
    }

    private function sealTimeEntry(TimeEntry $entry, string $systemNote): void
    {
        $endedAt = now();
        $entry->update([
            'ended_at' => $endedAt,
            'duration_seconds' => $entry->started_at->diffInSeconds($endedAt),
            'description' => $entry->description ?? $systemNote,
        ]);
    }
}
