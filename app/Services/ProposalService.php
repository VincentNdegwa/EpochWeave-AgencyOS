<?php

namespace App\Services;

use App\Models\Proposal;
use Exception;
use Illuminate\Support\Collection;

class ProposalService
{
    public function createProposal(array $data): Proposal
    {
        try {
            return Proposal::create($data);
        } catch (\Exception $e) {
            throw new Exception('Failed to create proposal: ' . $e->getMessage());
        }
    }

    public function updateProposal(Proposal $proposal, array $data): Proposal
    {
        try {
            $proposal->update($data);
            return $proposal->fresh();
        } catch (\Exception $e) {
            throw new Exception('Failed to update proposal: ' . $e->getMessage());
        }
    }

    public function deleteProposal(Proposal $proposal): void
    {
        try {
            $proposal->delete();
        } catch (\Exception $e) {
            throw new Exception('Failed to delete proposal: ' . $e->getMessage());
        }
    }

    public function getProposalById(int $id): ?Proposal
    {
        return Proposal::find($id);
    }

    public function getProposalsByWorkspace(int $workspaceId): Collection
    {
        return Proposal::where('workspace_id', $workspaceId)
            ->with('account')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getProposalsByAccount(int $accountId): Collection
    {
        return Proposal::where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateBlocks(Proposal $proposal, array $blocks): Proposal
    {
        try {
            $proposal->update(['blocks' => $blocks]);
            return $proposal->fresh();
        } catch (\Exception $e) {
            throw new Exception('Failed to update proposal blocks: ' . $e->getMessage());
        }
    }

    public function addBlock(Proposal $proposal, array $block): Proposal
    {
        try {
            $blocks = $proposal->blocks ?? [];
            $blocks[] = $block;
            return $this->updateBlocks($proposal, $blocks);
        } catch (\Exception $e) {
            throw new Exception('Failed to add block: ' . $e->getMessage());
        }
    }

    public function updateBlock(Proposal $proposal, string $blockId, array $blockData): Proposal
    {
        try {
            $blocks = $proposal->blocks ?? [];
            $blocks = array_map(function ($block) use ($blockId, $blockData) {
                if ($block['id'] === $blockId) {
                    return array_merge($block, $blockData);
                }
                return $block;
            }, $blocks);
            return $this->updateBlocks($proposal, $blocks);
        } catch (\Exception $e) {
            throw new Exception('Failed to update block: ' . $e->getMessage());
        }
    }

    public function deleteBlock(Proposal $proposal, string $blockId): Proposal
    {
        try {
            $blocks = $proposal->blocks ?? [];
            $blocks = array_filter($blocks, function ($block) use ($blockId) {
                return $block['id'] !== $blockId;
            });
            $blocks = array_values($blocks); // Re-index array
            return $this->updateBlocks($proposal, $blocks);
        } catch (\Exception $e) {
            throw new Exception('Failed to delete block: ' . $e->getMessage());
        }
    }

    public function reorderBlocks(Proposal $proposal, array $blockIds): Proposal
    {
        try {
            $blocks = $proposal->blocks ?? [];
            $blockMap = collect($blocks)->keyBy('id')->toArray();
            $reorderedBlocks = [];

            foreach ($blockIds as $index => $blockId) {
                if (isset($blockMap[$blockId])) {
                    $block = $blockMap[$blockId];
                    $block['sort_order'] = $index;
                    $reorderedBlocks[] = $block;
                }
            }

            return $this->updateBlocks($proposal, $reorderedBlocks);
        } catch (\Exception $e) {
            throw new Exception('Failed to reorder blocks: ' . $e->getMessage());
        }
    }
}
