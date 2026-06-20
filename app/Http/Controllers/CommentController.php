<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Services\CommentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService,
    ) {}

    public function store(Request $request, Task $task): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        try {
            $this->commentService->addTaskComment($task, [
                'user_id' => $request->user()->id,
                'body' => $validated['body'],
                'is_internal' => $validated['is_internal'] ?? false,
            ]);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Task $task, Comment $comment): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($task->workspace_id !== $workspace->id || $comment->commentable_id !== $task->id || $comment->commentable_type !== Task::class) {
            abort(404);
        }

        try {
            $this->commentService->deleteComment($comment);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}
