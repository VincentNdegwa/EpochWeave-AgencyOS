<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Task;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AttachmentService
{
    public function attachToTask(Task $task, UploadedFile $file, int $userId, string $disk = 'public'): Attachment
    {
        try {
            $path = $file->store('attachments/'.$task->workspace_id, $disk);

            return $task->attachments()->create([
                'workspace_id' => $task->workspace_id,
                'uploaded_by' => $userId,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'meta' => null,
            ]);
        } catch (Throwable $e) {
            throw new Exception('Failed to upload attachment: '.$e->getMessage(), 0, $e);
        }
    }

    public function deleteAttachment(Attachment $attachment): void
    {
        try {
            Storage::disk($attachment->disk)->delete($attachment->path);
            $attachment->delete();
        } catch (Throwable $e) {
            throw new Exception('Failed to delete attachment: '.$e->getMessage(), 0, $e);
        }
    }
}
