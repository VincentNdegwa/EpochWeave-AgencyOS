<?php

namespace App\Services;

use App\Models\InvoiceStatus;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class InvoiceStatusService
{
    public function listForWorkspace(int $workspaceId): Collection
    {
        return InvoiceStatus::query()
            ->where('workspace_id', $workspaceId)
            ->orderBy('position')
            ->get();
    }

    public function createStatus(array $data): InvoiceStatus
    {
        if (! isset($data['position'])) {
            $maxPosition = InvoiceStatus::where('workspace_id', $data['workspace_id'])->max('position') ?? 0;
            $data['position'] = $maxPosition + 1;
        }

        return InvoiceStatus::create($data);
    }

    public function updateStatus(InvoiceStatus $status, array $data): InvoiceStatus
    {
        $status->update($data);

        return $status->fresh();
    }

    public function deleteStatus(InvoiceStatus $status): void
    {
        if ($status->is_system) {
            throw new Exception('Cannot delete system invoice statuses.');
        }

        $status->delete();
    }

    public function getStatusByTrigger(int $workspaceId, string $trigger): ?InvoiceStatus
    {
        return InvoiceStatus::where('workspace_id', $workspaceId)
            ->where('automation_trigger', $trigger)
            ->first();
    }
}
