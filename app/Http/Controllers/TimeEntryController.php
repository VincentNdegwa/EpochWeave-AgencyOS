<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeEntryRequest;
use App\Http\Requests\UpdateTimeEntryRequest;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Services\TimeEntryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function __construct(
        private TimeEntryService $timeEntryService,
    ) {}

    public function store(StoreTimeEntryRequest $request, Task $task): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        try {
            $this->timeEntryService->createTimeEntry($task, $data);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateTimeEntryRequest $request, Task $task, TimeEntry $timeEntry): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id || $timeEntry->task_id !== $task->id) {
            abort(404);
        }

        try {
            $this->timeEntryService->updateTimeEntry($timeEntry, $request->validated());

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Task $task, TimeEntry $timeEntry): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id || $timeEntry->task_id !== $task->id) {
            abort(404);
        }

        try {
            $this->timeEntryService->deleteTimeEntry($timeEntry);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}
