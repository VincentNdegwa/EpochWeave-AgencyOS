<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectStatusRequest;
use App\Http\Requests\UpdateProjectStatusRequest;
use App\Models\ProjectStatus;
use App\Services\ProjectStatusService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectStatusController extends Controller
{
    public function __construct(private readonly ProjectStatusService $projectStatusService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $statuses = $this->projectStatusService->listForWorkspace($workspace->id);

        return Inertia::render('setup/project-status/index', [
            'project_statuses' => $statuses,
        ]);
    }

    public function store(StoreProjectStatusRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $this->projectStatusService->createStatus($data);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Project status created successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateProjectStatusRequest $request, ProjectStatus $projectStatus): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($projectStatus->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->projectStatusService->updateStatus($projectStatus, $request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Project status updated successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, ProjectStatus $projectStatus): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($projectStatus->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->projectStatusService->deleteStatus($projectStatus);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Project status deleted successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}
