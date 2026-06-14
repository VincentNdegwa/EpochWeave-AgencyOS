<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
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

        return Inertia::render('projects/index', [
            'projects' => $projects,
            'project_statuses' => ProjectStatus::cases(),
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
            'members.user:id,name,email',
        ]);

        return Inertia::render('projects/show', [
            'project' => $project,
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
