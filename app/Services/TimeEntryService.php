<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class TimeEntryService
{
    public function createTimeEntry(Task $task, array $data): TimeEntry
    {
        try {
            return DB::transaction(function () use ($task, $data) {
                $startedAt = $data['started_at'] instanceof Carbon ? $data['started_at'] : Carbon::parse($data['started_at']);
                $endedAt = $data['ended_at'] instanceof Carbon ? $data['ended_at'] : Carbon::parse($data['ended_at']);
                $durationSeconds = abs($endedAt->diffInSeconds($startedAt));

                return TimeEntry::create([
                    'workspace_id' => $task->workspace_id,
                    'project_id' => $task->project_id,
                    'task_id' => $task->id,
                    'user_id' => $data['user_id'],
                    'description' => $data['description'] ?? null,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'duration_seconds' => $durationSeconds,
                    'is_billable' => $data['is_billable'] ?? true,
                    'hourly_rate' => $data['hourly_rate'] ?? null,
                    'date' => $data['date'],
                ]);
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to create time entry: '.$e->getMessage(), 0, $e);
        }
    }

    public function updateTimeEntry(TimeEntry $timeEntry, array $data): TimeEntry
    {
        try {
            return DB::transaction(function () use ($timeEntry, $data) {
                if (isset($data['started_at']) && isset($data['ended_at'])) {
                    $startedAt = $data['started_at'] instanceof Carbon ? $data['started_at'] : Carbon::parse($data['started_at']);
                    $endedAt = $data['ended_at'] instanceof Carbon ? $data['ended_at'] : Carbon::parse($data['ended_at']);
                    $data['duration_seconds'] = abs($endedAt->diffInSeconds($startedAt));
                }

                $timeEntry->update($data);

                return $timeEntry->fresh();
            });
        } catch (Throwable $e) {
            throw new Exception('Failed to update time entry: '.$e->getMessage(), 0, $e);
        }
    }

    public function deleteTimeEntry(TimeEntry $timeEntry): void
    {
        try {
            $timeEntry->delete();
        } catch (Throwable $e) {
            throw new Exception('Failed to delete time entry: '.$e->getMessage(), 0, $e);
        }
    }
}
