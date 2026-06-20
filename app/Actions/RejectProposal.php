<?php

namespace App\Actions;

use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\User;
use App\Notifications\ProposalDeclined;
use App\Services\ActivityService;
use App\Services\WorkspaceSettingService;
use App\Models\WorkspaceSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RejectProposal
{
    public function __construct(
        private WorkspaceSettingService $workspaceSettingService,
        private ActivityService $activityService,
    ) {}

    public function reject(Proposal $proposal, ?string $reason = null): Proposal
    {
        try {
            return DB::transaction(function () use ($proposal, $reason) {
                $rejectedStatus = ProposalStatus::where('workspace_id', $proposal->workspace_id)
                    ->where('automation_trigger', 'declined')
                    ->first();

                if ($rejectedStatus) {
                    $proposal->proposal_status_id = $rejectedStatus->id;
                }

                $proposal->update([
                    'decided_at' => now(),
                    'decline_reason' => $reason,
                ]);

                $this->activityService->declined($proposal, "Proposal '{$proposal->title}' was declined.");
                $this->sendDeclinedNotification($proposal, $reason);

                return $proposal->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Failed to reject proposal', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    private function sendDeclinedNotification(Proposal $proposal, ?string $reason): void
    {
        try {
            $settings = $this->workspaceSettingService->getOrCreate(
                $proposal->workspace_id,
                WorkspaceSetting::SUBMODULE_NOTIFICATIONS
            );

            $notificationSettings = $settings->settings['notifications'] ?? [];
            $proposalNotifications = $notificationSettings['proposals'] ?? [];

            if ($proposalNotifications['declined'] ?? false) {
                if ($proposal->user_id) {
                    $proposal->user->notify(new ProposalDeclined($proposal));
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break the user experience
            Log::error('Failed to send declined notification', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
