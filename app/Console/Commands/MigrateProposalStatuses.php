<?php

namespace App\Console\Commands;

use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\Workspace;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:migrate-proposal-statuses')]
#[Description('Migrate existing proposals to use the new status system')]
class MigrateProposalStatuses extends Command
{
    public function handle()
    {
        $this->info('Migrating proposals to new status system...');
        
        $workspaces = Workspace::all();
        $migratedCount = 0;
        
        foreach ($workspaces as $workspace) {
            $this->info("Processing workspace: {$workspace->id}");
            
            $proposals = Proposal::where('workspace_id', $workspace->id)
                ->whereNull('proposal_status_id')
                ->get();
            
            if ($proposals->isEmpty()) {
                $this->info("  No proposals to migrate for workspace {$workspace->id}");
                continue;
            }
            
            $statusMap = $this->getStatusMap($workspace);
            
            foreach ($proposals as $proposal) {
                $statusId = $statusMap[$proposal->status] ?? null;
                
                if ($statusId) {
                    $proposal->update(['proposal_status_id' => $statusId]);
                    $migratedCount++;
                    $this->info("  Migrated proposal {$proposal->id}: {$proposal->status} -> status_id {$statusId}");
                } else {
                    $this->warn("  No status mapping found for proposal {$proposal->id}: {$proposal->status}");
                }
            }
        }
        
        $this->info("Migration complete! Migrated {$migratedCount} proposals.");
        
        return 0;
    }
    
    private function getStatusMap(Workspace $workspace): array
    {
        $statuses = ProposalStatus::where('workspace_id', $workspace->id)
            ->whereNotNull('automation_trigger')
            ->get();
        
        $map = [];
        foreach ($statuses as $status) {
            $map[$status->automation_trigger] = $status->id;
        }
        
        return $map;
    }
}
