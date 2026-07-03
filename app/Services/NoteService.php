<?php

namespace App\Services;

use App\Models\Note;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class NoteService
{
    public function createNote(Model $noteable, array $data): Note
    {
        try {
            return DB::transaction(function () use ($noteable, $data) {
                return $noteable->notes()->create([
                    'workspace_id' => $data['workspace_id'],
                    'user_id' => $data['user_id'],
                    'body' => $data['body'],
                    'is_internal' => $data['is_internal'] ?? false,
                    'pinned_at' => $data['pinned_at'] ?? null,
                ])->load('user');
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to create note: '.$e->getMessage(), 0, $e);
        }
    }

    public function updateNote(Note $note, array $data): Note
    {
        try {
            return DB::transaction(function () use ($note, $data) {
                $note->update([
                    'body' => $data['body'],
                    'is_internal' => $data['is_internal'] ?? $note->is_internal,
                    'pinned_at' => $data['pinned_at'] ?? $note->pinned_at,
                ]);

                return $note->fresh(['user']);
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to update note: '.$e->getMessage(), 0, $e);
        }
    }

    public function deleteNote(Note $note): void
    {
        try {
            $note->delete();
        } catch (Throwable $e) {
            throw new Exception('Failed to delete note: '.$e->getMessage(), 0, $e);
        }
    }
}
