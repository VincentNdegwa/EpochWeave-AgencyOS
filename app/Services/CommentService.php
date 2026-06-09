<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Task;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommentService
{
    public function addTaskComment(Task $task, array $data): Comment
    {
        try {
            return DB::transaction(function () use ($task, $data) {
                return $task->comments()->create([
                    'workspace_id' => $task->workspace_id,
                    'project_id' => $task->project_id,
                    'user_id' => $data['user_id'],
                    'body' => $data['body'],
                    'is_internal' => $data['is_internal'] ?? false,
                    'pinned_at' => $data['pinned_at'] ?? null,
                ])->load('user');
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to add comment: '.$e->getMessage(), 0, $e);
        }
    }

    public function deleteComment(Comment $comment): void
    {
        try {
            $comment->delete();
        } catch (Throwable $e) {
            throw new Exception('Failed to delete comment: '.$e->getMessage(), 0, $e);
        }
    }
}
