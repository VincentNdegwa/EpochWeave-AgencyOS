<?php

namespace App\Actions;

use App\Models\Proposal;
use App\Models\ProposalView;
use App\Models\WorkspaceSetting;
use App\Notifications\ProposalRevisited;
use App\Notifications\ProposalViewed;
use App\Services\ActivityService;
use App\Services\WorkspaceSettingService;
use Exception;
use Illuminate\Support\Facades\Log;

class TrackProposalView
{
    public function __construct(
        private WorkspaceSettingService $workspaceSettingService,
        private ActivityService $activityService,
    ) {}

    public function execute(Proposal $proposal, $viewer = null): void
    {
        try {
            $settings = $this->workspaceSettingService->getOrCreate(
                $proposal->workspace_id,
                WorkspaceSetting::SUBMODULE_NOTIFICATIONS
            );

            $notificationSettings = $settings->settings['notifications'] ?? [];
            $proposalNotifications = $notificationSettings['proposals'] ?? [];

            $viewedEnabled = $proposalNotifications['viewed'] ?? false;
            $revisitedEnabled = $proposalNotifications['revisited'] ?? false;

            $existingView = ProposalView::where('proposal_id', $proposal->id)
                ->when($viewer, fn ($query) => $query->where('viewer_id', $viewer->id)->where('viewer_type', get_class($viewer)))
                ->orderBy('created_at', 'desc')
                ->first();

            $now = now();

            if (! $existingView) {
                ProposalView::create([
                    'proposal_id' => $proposal->id,
                    'viewer_id' => $viewer?->id,
                    'viewer_type' => $viewer ? get_class($viewer) : null,
                    'viewed_at' => $now,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                $viewerName = $viewer?->first_name ?? 'a visitor';
                $this->activityService->viewed($proposal, "Proposal '{$proposal->title}' was viewed by {$viewerName}.");

                if ($viewedEnabled && $proposal->user_id) {
                    $proposal->user->notify(new ProposalViewed($proposal, $viewer));
                }
            } else {
                $hoursSinceLastView = $existingView->viewed_at->diffInHours($now);

                if ($hoursSinceLastView >= 48) {
                    ProposalView::create([
                        'proposal_id' => $proposal->id,
                        'viewer_id' => $viewer?->id,
                        'viewer_type' => $viewer ? get_class($viewer) : null,
                        'viewed_at' => $now,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'is_revisit' => true,
                    ]);

                    if ($revisitedEnabled && $proposal->user_id) {
                        $proposal->user->notify(new ProposalRevisited(
                            $proposal,
                            $viewer,
                            $existingView->viewed_at
                        ));
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to track proposal view', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
