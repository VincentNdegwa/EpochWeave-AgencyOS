<?php

namespace App\Services;

use App\Models\Comment;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommentService
{
    public function createComment(Model $commentable, array $data): Comment
    {
        try {
            return DB::transaction(function () use ($commentable, $data) {
                return $commentable->comments()->create([
                    'workspace_id' => $data['workspace_id'],
                    'user_id' => $data['user_id'],
                    'body' => $data['body'],
                    'is_internal' => $data['is_internal'] ?? false,
                    'pinned_at' => $data['pinned_at'] ?? null,
                ])->load('user');
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to create comment: '.$e->getMessage(), 0, $e);
        }
    }

    public function updateComment(Comment $comment, array $data): Comment
    {
        try {
            return DB::transaction(function () use ($comment, $data) {
                $comment->update([
                    'body' => $data['body'],
                    'is_internal' => $data['is_internal'] ?? $comment->is_internal,
                    'pinned_at' => $data['pinned_at'] ?? $comment->pinned_at,
                ]);

                return $comment->fresh(['user']);
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to update comment: '.$e->getMessage(), 0, $e);
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
