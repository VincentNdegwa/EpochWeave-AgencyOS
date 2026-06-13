<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionProjectFromProposal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Proposal $proposal
    ) {}

    public function handle(): void
    {
        try {
            if ($this->proposal->project_id !== null) {
                Log::info('Proposal already has a project attached, skipping provisioning', [
                    'proposal_id' => $this->proposal->id,
                    'project_id' => $this->proposal->project_id,
                ]);

                return;
            }

            $project = Project::create([
                'workspace_id' => $this->proposal->workspace_id,
                'account_id' => $this->proposal->account_id,
                'name' => $this->proposal->title,
                'description' => null,
                'status' => 'active',
                'currency' => $this->proposal->currency ?? 'USD',
                'start_date' => now(),
                'due_date' => now()->addMonths(3),
                'portal_visible' => false,
            ]);

            $this->proposal->update(['project_id' => $project->id]);

            Log::info('Project provisioned from proposal', [
                'proposal_id' => $this->proposal->id,
                'project_id' => $project->id,
                'workspace_id' => $this->proposal->workspace_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to provision project from proposal', [
                'proposal_id' => $this->proposal->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
