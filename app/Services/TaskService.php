<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class TaskService
{
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

                if (isset($data['task_status_id'])) {
                    $status = $this->getStatusForProject($project, (int) $data['task_status_id']);
                    $data['task_status_id'] = $status->id;
                } else {
                    $status = $task->status;
                }

                $task->update($data);

                if (array_key_exists('tag_ids', $data)) {
                    $this->syncTags($task, $data['tag_ids'] ?? []);
                }

                if (isset($status)) {
                    $this->updateCompletionState($task, $status);
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
        $status = $project->taskStatuses()->whereKey($statusId)->first();

        if (! $status) {
            throw new Exception('Invalid task status selected for this project.');
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
}
