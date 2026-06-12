<?php

namespace App\Services;

use App\Models\ProposalStatus;

class MovementRulesService
{
    public function getValidTargets(string $sourceTrigger, array $proposalStatuses): array
    {
        switch ($sourceTrigger) {
            case 'draft':
                return collect($proposalStatuses)
                    ->filter(fn($status) => in_array($status['automation_trigger'] ?? '', ['draft', 'sent']))
                    ->pluck('id')
                    ->toArray();
            case 'sent':
                return collect($proposalStatuses)
                    ->filter(fn($status) => in_array($status['automation_trigger'] ?? '', ['draft', 'sent', 'accepted', 'declined']))
                    ->pluck('id')
                    ->toArray();
            case 'accepted':
                return collect($proposalStatuses)
                    ->filter(fn($status) => ($status['automation_trigger'] ?? '') === 'accepted')
                    ->pluck('id')
                    ->toArray();
            case 'declined':
            case 'expired':
                return collect($proposalStatuses)
                    ->filter(fn($status) => ($status['automation_trigger'] ?? '') === 'draft')
                    ->pluck('id')
                    ->toArray();
            default:
                return [];
        }
    }

    public function canMove(string $currentTrigger, string $targetTrigger, array $proposalStatuses): array
    {
        if ($targetTrigger === 'expired') {
            return [
                'allowed' => false,
                'error' => 'The Expired state is handled exclusively by system automated background timers.'
            ];
        }

        if ($currentTrigger === 'accepted' && $targetTrigger !== 'accepted') {
            return [
                'allowed' => false,
                'error' => 'This proposal has already been accepted and locked. You must explicitly void the active contract or invoice to reset this pipeline position.'
            ];
        }
        
        // Get valid targets for the current trigger
        $validTargets = $this->getValidTargets($currentTrigger, $proposalStatuses);
        
        // Find the target status ID for the target trigger
        $targetStatus = collect($proposalStatuses)
            ->firstWhere('automation_trigger', $targetTrigger);
        
        if (!$targetStatus) {
            return [
                'allowed' => false,
                'error' => 'Invalid target status.'
            ];
        }
        
        // Check if the target status is in the valid targets
        if (!in_array($targetStatus['id'], $validTargets)) {
            return [
                'allowed' => false,
                'error' => 'Cannot move from ' . ucfirst($currentTrigger) . ' to ' . ucfirst($targetTrigger) . '.'
            ];
        }

        return ['allowed' => true];
    }

    public function getMovementRules(array $proposalStatuses): array
    {
        $rules = [];
        $triggers = collect($proposalStatuses)->pluck('automation_trigger')->filter()->unique();

        foreach ($triggers as $trigger) {
            $rules[$trigger] = $this->getValidTargets($trigger, $proposalStatuses);
        }

        return $rules;
    }
}
