<?php

namespace App\Observers;

use App\Models\InvoiceStatus;
use App\Models\ProjectStatus;
use App\Models\ProposalStatus;
use App\Models\TaskStatus;
use App\Models\Workspace;

class WorkspaceObserver
{
    public function created(Workspace $workspace): void
    {
        // Create Proposal Statuses
        foreach (ProposalStatus::getDefaultStatuses() as $statusData) {
            ProposalStatus::create([
                'workspace_id' => $workspace->id,
                ...$statusData,
            ]);
        }

        // Create Project Statuses
        foreach (ProjectStatus::getDefaultStatuses() as $statusData) {
            ProjectStatus::create([
                'workspace_id' => $workspace->id,
                ...$statusData,
            ]);
        }

        // Create Task Statuses
        foreach (TaskStatus::getDefaultStatuses() as $statusData) {
            TaskStatus::create([
                'workspace_id' => $workspace->id,
                ...$statusData,
            ]);
        }

        // Create Invoice Statuses
        foreach (InvoiceStatus::getDefaultStatuses() as $statusData) {
            InvoiceStatus::create([
                'workspace_id' => $workspace->id,
                ...$statusData,
            ]);
        }
    }
}
