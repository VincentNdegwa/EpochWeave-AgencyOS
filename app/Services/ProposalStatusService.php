<?php

namespace App\Services;

use App\Models\ProposalStatus;
use App\Models\Workspace;
use Exception;

class ProposalStatusService
{
    public function getStatusesForWorkspace(Workspace $workspace): \Illuminate\Database\Eloquent\Collection
    {
        return $workspace->proposalStatuses()->orderBy('position')->get();
    }

    public function createStatus(Workspace $workspace, array $data): ProposalStatus
    {
        try {
            return $workspace->proposalStatuses()->create($data);
        } catch (Exception $e) {
            throw new Exception('Failed to create proposal status: ' . $e->getMessage());
        }
    }

    public function updateStatus(ProposalStatus $status, array $data): ProposalStatus
    {
        try {
            $status->update($data);
            return $status->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update proposal status: ' . $e->getMessage());
        }
    }

    public function deleteStatus(ProposalStatus $status): bool
    {
        if ($status->is_system) {
            throw new Exception('Cannot delete system proposal statuses.');
        }

        $status->delete();
        return true;
    }

    public function reorderStatuses(Workspace $workspace, array $statusIds): void
    {
        $statuses = $workspace->proposalStatuses()
            ->whereIn('id', $statusIds)
            ->get()
            ->keyBy('id');

        foreach ($statusIds as $index => $statusId) {
            if (isset($statuses[$statusId])) {
                $statuses[$statusId]->update(['position' => $index]);
            }
        }
    }

    public function getStatusByTrigger(Workspace $workspace, string $trigger): ?ProposalStatus
    {
        return $workspace->proposalStatuses()
            ->where('automation_trigger', $trigger)
            ->first();
    }

    public function initializeDefaultStatuses(Workspace $workspace): void
    {
        $defaultStatuses = ProposalStatus::getDefaultStatuses();
        
        foreach ($defaultStatuses as $statusData) {
            $workspace->proposalStatuses()->create($statusData);
        }
    }
}
