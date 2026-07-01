<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskStatusRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\TaskStatus;
use App\Services\TaskStatusService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskStatusController extends Controller
{
    public function __construct(private readonly TaskStatusService $taskStatusService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $statuses = $this->taskStatusService->listForWorkspace($workspace->id);

        return Inertia::render('setup/task-status/index', [
            'task_statuses' => $statuses,
        ]);
    }

    public function store(StoreTaskStatusRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $this->taskStatusService->createStatus($data);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Task status created successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateTaskStatusRequest $request, TaskStatus $taskStatus): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($taskStatus->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->taskStatusService->updateStatus($taskStatus, $request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Task status updated successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, TaskStatus $taskStatus): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($taskStatus->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->taskStatusService->deleteStatus($taskStatus);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Task status deleted successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}
