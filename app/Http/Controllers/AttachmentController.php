<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\Task;
use App\Services\AttachmentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function __construct(
        private AttachmentService $attachmentService,
    ) {}

    public function store(StoreAttachmentRequest $request, Task $task): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->attachmentService->attachToTask(
                $task,
                $request->file('file'),
                $request->user()->id,
            );

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Task $task, Attachment $attachment): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id || $attachment->attachable_id !== $task->id || $attachment->attachable_type !== Task::class) {
            abort(404);
        }

        try {
            $this->attachmentService->deleteAttachment($attachment);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}
