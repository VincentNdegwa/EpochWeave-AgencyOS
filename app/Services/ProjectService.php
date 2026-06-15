<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectStatus;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProjectService
{
    public function listProjects(int $workspaceId, array $filters = []): Collection
    {
        $query = Project::query()
            ->with(['account:id,company_name', 'members.user:id,name', 'status:id,title,color'])
            ->where('workspace_id', $workspaceId);

        if (! empty($filters['status'])) {
            // Filter by status automation trigger via relationship
            $query->whereHas('status', function ($q) use ($filters) {
                $q->where('automation_trigger', $filters['status']);
            });
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
                $members = $data['members'] ?? [];

                unset($data['members']);

                $data['color'] = $data['color'] ?? '#6366f1';
                $data['portal_visible'] = $data['portal_visible'] ?? true;

                // If status not provided, get active status from workspace
                if (empty($data['project_status_id'])) {
                    $activeStatus = ProjectStatus::where('workspace_id', $data['workspace_id'])
                        ->where('automation_trigger', 'active')
                        ->first();
                    $data['project_status_id'] = $activeStatus?->id;
                }

                $project = Project::create($data);

                $this->syncMembers($project, $members);

                return $project->fresh(['account', 'members.user']);
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
                $members = $membersProvided ? ($data['members'] ?? []) : null;

                unset($data['members']);

                $project->update($data);

                if ($membersProvided) {
                    $this->syncMembers($project, $members ?? []);
                }

                return $project->fresh(['account', 'members.user']);
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
}
