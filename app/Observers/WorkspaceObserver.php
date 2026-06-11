<?php

namespace App\Observers;

use App\Models\ProposalStatus;
use App\Models\Workspace;

class WorkspaceObserver
{
    public function created(Workspace $workspace): void
    {
        $defaultStatuses = ProposalStatus::getDefaultStatuses();
        
        foreach ($defaultStatuses as $statusData) {
            ProposalStatus::create([
                'workspace_id' => $workspace->id,
                ...$statusData,
            ]);
        }
    }
}
