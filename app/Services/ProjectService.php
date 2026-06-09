<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMember;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProjectService
{
    public function listProjects(int $workspaceId, array $filters = []): Collection
    {
        $query = Project::query()
            ->with(['account:id,company_name', 'members.user:id,name'])
            ->where('workspace_id', $workspaceId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function getProjectById(int $projectId, int $workspaceId): ?Project
    {
        return Project::query()
            ->where('workspace_id', $workspaceId)
            ->with([
                'account',
                'members.user:id,name,email',
                'taskStatuses' => fn ($query) => $query->orderBy('position'),
                'tasks' => fn ($query) => $query
                    ->with(['status', 'assignee:id,name,email', 'tags', 'comments.user:id,name', 'attachments'])
                    ->orderBy('position'),
            ])
            ->find($projectId);
    }

    public function createProject(array $data): Project
    {
        try {
            return DB::transaction(function () use ($data) {
                $statuses = $data['statuses'] ?? null;
                $members = $data['members'] ?? [];

                unset($data['statuses'], $data['members']);

                $data['color'] = $data['color'] ?? '#6366f1';
                $data['status'] = $data['status'] ?? 'active';
                $data['portal_visible'] = $data['portal_visible'] ?? true;

                $project = Project::create($data);

                $this->syncMembers($project, $members);
                $this->syncTaskStatuses($project, $statuses);

                return $project->fresh(['account', 'members.user', 'taskStatuses']);
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to create project: '.$e->getMessage(), 0, $e);
        }
    }

    public function updateProject(Project $project, array $data): Project
    {
        try {
            return DB::transaction(function () use ($project, $data) {
                $membersProvided = array_key_exists('members', $data);
                $statusesProvided = array_key_exists('statuses', $data);

                $members = $membersProvided ? ($data['members'] ?? []) : null;
                $statuses = $statusesProvided ? ($data['statuses'] ?? null) : null;

                unset($data['members'], $data['statuses']);

                $project->update($data);

                if ($membersProvided) {
                    $this->syncMembers($project, $members ?? []);
                }

                if ($statusesProvided) {
                    $this->syncTaskStatuses($project, $statuses);
                }

                return $project->fresh(['account', 'members.user', 'taskStatuses']);
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to update project: '.$e->getMessage(), 0, $e);
        }
    }

    public function deleteProject(Project $project): void
    {
        try {
            $project->delete();
        } catch (Throwable $e) {
            throw new Exception('Failed to delete project: '.$e->getMessage(), 0, $e);
        }
    }

    private function syncMembers(Project $project, array $members): void
    {
        $project->members()->delete();

        if (empty($members)) {
            return;
        }

        $rows = collect($members)
            ->filter(fn ($member) => isset($member['user_id']))
            ->map(fn ($member) => [
                'project_id' => $project->id,
                'user_id' => $member['user_id'],
                'role' => $member['role'] ?? 'member',
                'hourly_rate' => $member['hourly_rate'] ?? null,
                'joined_at' => now(),
            ])->all();

        if (! empty($rows)) {
            ProjectMember::query()->insert($rows);
        }
    }

    private function syncTaskStatuses(Project $project, ?array $statuses): void
    {
        $project->taskStatuses()->delete();

        $payload = $statuses ?: $this->defaultStatuses();

        foreach ($payload as $position => $status) {
            $project->taskStatuses()->create([
                'name' => $status['name'],
                'color' => $status['color'] ?? '#6366f1',
                'position' => $status['position'] ?? $position,
                'is_default' => $status['is_default'] ?? $position === 0,
                'is_closed' => $status['is_closed'] ?? false,
            ]);
        }
    }

    private function defaultStatuses(): array
    {
        return [
            ['name' => 'Backlog', 'color' => '#94a3b8', 'is_default' => true, 'is_closed' => false],
            ['name' => 'In Progress', 'color' => '#6366f1', 'is_default' => false, 'is_closed' => false],
            ['name' => 'Review', 'color' => '#f97316', 'is_default' => false, 'is_closed' => false],
            ['name' => 'Completed', 'color' => '#22c55e', 'is_default' => false, 'is_closed' => true],
        ];
    }
}
