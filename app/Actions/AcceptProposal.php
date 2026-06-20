<?php

namespace App\Actions;

use App\Jobs\CreateInvoiceFromProposal;
use App\Jobs\ProvisionProjectFromProposal;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\WorkspaceSetting;
use App\Notifications\ProposalSigned;
use App\Services\ActivityService;
use App\Services\WorkspaceSettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcceptProposal
{
    public function __construct(
        private WorkspaceSettingService $workspaceSettingService,
        private ActivityService $activityService,
    ) {}

    public function accept(Proposal $proposal, array $data): Proposal
    {
        return DB::transaction(function () use ($proposal, $data) {
            $acceptedStatus = ProposalStatus::where('workspace_id', $proposal->workspace_id)
                ->where('automation_trigger', 'accepted')
                ->first();

            if ($acceptedStatus) {
                $proposal->proposal_status_id = $acceptedStatus->id;
            }

            $proposal->update([
                'accepted_at' => now(),
                'decided_at' => now(),
                'signer_name' => $data['name'] ?? null,
                'signed_at' => now(),
                'signature_data' => ($data['signature'] ?? null) ? ['data' => $data['signature']] : null,
                'signed_ip' => request()->ip(),
                'signed_user_agent' => request()->userAgent(),
            ]);

            $signerName = $data['name'] ?? 'Client';
            $this->activityService->accepted($proposal, "Proposal '{$proposal->title}' was accepted by {$signerName}.");
            $this->sendSignedNotification($proposal, $data);
            $this->dispatchAutomationJobs($proposal);

            return $proposal->fresh();
        });
    }

    private function dispatchAutomationJobs(Proposal $proposal): void
    {
        try {
            $settings = $this->workspaceSettingService->getOrCreate(
                $proposal->workspace_id,
                WorkspaceSetting::SUBMODULE_AUTOMATION,
            );

            $automationSettings = $settings->settings['proposals'] ?? [];

            if ($automationSettings['auto_generate_invoice'] ?? true) {
                CreateInvoiceFromProposal::dispatch($proposal);
            }

            if ($automationSettings['auto_create_project'] ?? true) {
                ProvisionProjectFromProposal::dispatch($proposal);
            }
        } catch (\Exception $e) {
            Log::error('Failed to dispatch proposal automation jobs', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendSignedNotification(Proposal $proposal, array $data): void
    {
        try {
            $settings = $this->workspaceSettingService->getOrCreate(
                $proposal->workspace_id,
                WorkspaceSetting::SUBMODULE_NOTIFICATIONS
            );

            $notificationSettings = $settings->settings['notifications'] ?? [];
            $proposalNotifications = $notificationSettings['proposals'] ?? [];

            if ($proposalNotifications['signed'] ?? false) {
                $signer = new class
                {
                    public $id = null;

                    public $first_name;

                    public function __construct()
                    {
                        $this->first_name = 'Client';
                    }
                };
                $signer->first_name = $data['name'] ?? 'Client';

                if ($proposal->user_id) {
                    $proposal->user->notify(new ProposalSigned($proposal, $signer));
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break the user experience
            Log::error('Failed to send signed notification', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
