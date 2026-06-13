<?php

namespace App\Actions;

use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Notifications\ProposalSigned;
use App\Services\WorkspaceSettingService;
use App\Models\WorkspaceSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcceptProposal
{
    public function __construct(
        private WorkspaceSettingService $workspaceSettingService
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
                'signature_data' => $data['signature'] ? ['data' => $data['signature']] : null,
                'signed_ip' => request()->ip(),
                'signed_user_agent' => request()->userAgent(),
            ]);

            $this->sendSignedNotification($proposal, $data);

            return $proposal->fresh();
        });
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
                $signer = new class {
                    public $id = null;
                    public $first_name;
                    
                    public function __construct() {
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
