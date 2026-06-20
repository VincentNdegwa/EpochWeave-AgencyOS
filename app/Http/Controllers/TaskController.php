<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Services\TaskService;
use App\Services\UserPreferenceService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly UserPreferenceService $userPreferenceService,
    ) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $user = $request->user();
        $filters = $request->only(['status', 'search', 'project_id']);

        $query = Task::query()
            ->with([
                'project:id,name,color',
                'status:id,title,color',
                'assignee:id,name',
                'tags:id,name,color',
            ])
            ->where('workspace_id', $workspace->id);

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['status'])) {
            $query->where('task_status_id', $filters['status']);
        }

        $tasks = $query->orderBy('position')->get();

        $taskStatuses = TaskStatus::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('position')
            ->get(['id', 'title', 'color']);

        $tags = Tag::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        $displayMode = $this->userPreferenceService->getDisplayMode($workspace->id, $user->id);

        return Inertia::render('tasks/index', [
            'tasks' => $tasks,
            'task_statuses' => $taskStatuses,
            'tags' => $tags,
            'display_mode' => $displayMode,
            'filters' => $filters,
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $validated = $request->validated();

        $projectId = $validated['project_id'] ?? $request->input('project_id');

        if (empty($projectId)) {
            abort(422, 'Project is required.');
        }

        $data = array_merge($validated, [
            'workspace_id' => $workspace->id,
            'created_by' => $request->user()?->id,
        ]);

        $project = Project::where('workspace_id', $workspace->id)
            ->findOrFail($projectId);

        try {
            $task = $this->taskService->createTask($project, $data);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Task created successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function show(Request $request, Task $task): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        $task->load([
            'project:id,name,color',
            'status:id,title,color,automation_trigger',
            'assignee:id,name,email',
            'tags:id,name,color',
            'comments.user:id,name',
            'attachments.uploader:id,name',
            'timeEntries.user:id,name',
            'children:id,title,task_status_id,position',
            'children.status:id,title,color',
        ]);

        $activities = Activity::where('subject_type', Task::class)
            ->where('subject_id', $task->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return Inertia::render('tasks/show', [
            'task' => $task,
            'activities' => $activities,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->taskService->updateTask($task, $request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Task updated successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->taskService->deleteTask($task);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Task deleted successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'task_status_id' => ['required', 'exists:task_statuses,id'],
        ]);

        try {
            $this->taskService->updateTask($task, $validated);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Task status updated.']);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }

        return redirect()->back();
    }
}
