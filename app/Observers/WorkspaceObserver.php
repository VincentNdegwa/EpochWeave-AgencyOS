<?php

namespace App\Observers;

use App\Models\CompanySize;
use App\Models\Industry;
use App\Models\InvoiceStatus;
use App\Models\LeadSource;
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

        // Create Industries
        foreach (Industry::getDefaultRecords() as $recordData) {
            Industry::create([
                'workspace_id' => $workspace->id,
                'is_default' => true,
                ...$recordData,
            ]);
        }

        // Create Lead Sources
        foreach (LeadSource::getDefaultRecords() as $recordData) {
            LeadSource::create([
                'workspace_id' => $workspace->id,
                'is_default' => true,
                ...$recordData,
            ]);
        }

        // Create Company Sizes
        foreach (CompanySize::getDefaultRecords() as $recordData) {
            CompanySize::create([
                'workspace_id' => $workspace->id,
                'is_default' => true,
                ...$recordData,
            ]);
        }
    }
}
