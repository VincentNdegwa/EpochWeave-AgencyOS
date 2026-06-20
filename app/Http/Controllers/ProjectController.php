<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Activity;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\TaskStatus;
use App\Services\ProjectService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $filters = $request->only(['status', 'search']);
        $projects = $this->projectService->listProjects($workspace->id, $filters);

        // Get statuses for filter dropdown
        $projectStatuses = ProjectStatus::where('workspace_id', $workspace->id)
            ->orderBy('position')
            ->get()
            ->map(fn ($status) => [
                'value' => $status->automation_trigger ?? $status->id,
                'label' => $status->title,
                'color' => $status->color,
            ]);

        return Inertia::render('projects/index', [
            'projects' => $projects,
            'project_statuses' => $projectStatuses,
            'filters' => $filters,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $data = array_merge($request->validated(), [
            'workspace_id' => $workspace->id,
            'currency' => $workspace->currency ?? 'USD',
        ]);

        try {
            $project = $this->projectService->createProject($data);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Project created successfully.']);

            return redirect()->route('projects.show', $project);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function show(Request $request, Project $project): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $project->load([
            'account',
            'status',
            'members.user:id,name,email',
            'tasks' => function ($query) {
                $query->with(['status:id,title,color', 'assignee:id,name', 'tags:id,name,color'])
                    ->orderBy('position');
            },
        ]);

        $taskStatuses = TaskStatus::where('workspace_id', $workspace->id)
            ->orderBy('position')
            ->get(['id', 'title', 'color']);

        $activities = Activity::where('subject_type', Project::class)
            ->where('subject_id', $project->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return Inertia::render('projects/show', [
            'project' => $project,
            'task_statuses' => $taskStatuses,
            'activities' => $activities,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $data = array_merge($request->validated(), [
                'currency' => $workspace->currency ?? 'USD',
            ]);
            $this->projectService->updateProject($project, $data);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Project updated successfully.']);

            return redirect()->route('projects.show', $project);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->projectService->deleteProject($project);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Project deleted successfully.']);

            return redirect()->route('projects.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}
