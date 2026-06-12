<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\ProposalStatus;
use Exception;
use Illuminate\Support\Collection;

class ProposalService
{
    
    public function createProposalWithItems(array $data, array $items = []): Proposal
    {
        try {
            if (!isset($data['proposal_status_id'])) {
                $data['proposal_status_id'] = $this->getDraftStatusId($data['workspace_id']);
            }
            
            $proposal = Proposal::create($data);
            
            if (!empty($items)) {
                $this->createProposalItems($proposal, $items);
            }
            
            return $proposal->fresh(['items.product', 'proposalStatus', 'accountContact', 'user']);
        } catch (Exception $e) {
            throw new Exception('Failed to create proposal with items: '.$e->getMessage());
        }
    }

    public function createProposalItems(Proposal $proposal, array $items): void
    {
        try {
            // Prepare items for bulk insert to avoid N+1 queries
            $proposalItems = [];
            foreach ($items as $index => $item) {
                $proposalItems[] = [
                    'proposal_id' => $proposal->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['description'] ?? '',
                    'description' => $item['item_description'] ?? null,
                    'unit_label' => $item['unit'] ?? 'Pcs',
                    'billing_type' => $item['billing_type'] ?? 'one_time',
                    'billing_frequency' => $item['billing_frequency'] ?? 'none',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'subtotal' => $item['subtotal'] ?? 0,
                    'discount_type' => $item['item_discount_type'] ?? 'none',
                    'discount_value' => $item['item_discount_value'] ?? 0,
                    'discount_amount' => 0, // Calculate if needed
                    'total' => $item['subtotal'] ?? 0, // For now, same as subtotal
                    'is_optional' => $item['is_optional'] ?? false,
                    'is_selected' => true,
                    'position' => $index, // Use array index as position
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Bulk insert to avoid N+1 queries
            if (!empty($proposalItems)) {
                ProposalItem::insert($proposalItems);
            }
        } catch (Exception $e) {
            throw new Exception('Failed to create proposal items: '.$e->getMessage());
        }
    }

    public function updateProposal(Proposal $proposal, array $data, array $items = []): Proposal
    {
        try {
            $proposal->update($data);

            // Update line items if provided
            if (!empty($items)) {
                // Delete existing items and recreate them
                $proposal->items()->delete();
                $this->createProposalItems($proposal, $items);
            }

            return $proposal->fresh(['items.product']);
        } catch (Exception $e) {
            throw new Exception('Failed to update proposal: '.$e->getMessage());
        }
    }

    public function deleteProposal(Proposal $proposal): void
    {
        try {
            $proposal->delete();
        } catch (Exception $e) {
            throw new Exception('Failed to delete proposal: '.$e->getMessage());
        }
    }

    public function calculateTotalsFromLineItems(array $lineItems): array
    {
        $subtotal = 0;
        $discountTotal = 0;
        $taxAmount = 0;

        // Calculate subtotal from line items
        foreach ($lineItems as $item) {
            $itemSubtotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1);
            
            // Apply item-level discount
            $itemDiscountType = $item['item_discount_type'] ?? 'none';
            $itemDiscountValue = $item['item_discount_value'] ?? 0;
            
            if ($itemDiscountType === 'percentage' && $itemDiscountValue > 0) {
                $itemDiscountAmount = $itemSubtotal * ($itemDiscountValue / 100);
            } elseif ($itemDiscountType === 'fixed' && $itemDiscountValue > 0) {
                $itemDiscountAmount = $itemDiscountValue;
            } else {
                $itemDiscountAmount = 0;
            }
            
            $subtotal += $itemSubtotal - $itemDiscountAmount;
        }

        $grandTotal = $subtotal - $discountTotal + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
        ];
    }

    public function updateProposalTotals(Proposal $proposal, array $lineItems = null): Proposal
    {
        try {
            // Use provided line items or extract from content or get from proposal items
            if ($lineItems) {
                $items = $lineItems;
            } else {
                // Try to extract from pricing table blocks in content first
                $contentItems = $this->extractLineItemsFromContent($proposal->content);
                
                if (!empty($contentItems)) {
                    $items = $contentItems;
                } else {
                    // Fallback to proposal items
                    $items = $proposal->items->map(function ($item) {
                        return [
                            'unit_price' => $item->unit_price,
                            'quantity' => $item->quantity,
                            'item_discount_type' => $item->discount_type,
                            'item_discount_value' => $item->discount_value,
                        ];
                    })->toArray();
                }
            }

            $totals = $this->calculateTotalsFromLineItems($items);
            
            $proposal->update([
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_amount' => $totals['tax_amount'],
                'grand_total' => $totals['grand_total'],
            ]);

            return $proposal->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update proposal totals: '.$e->getMessage());
        }
    }

    /**
     * Extract line items from pricing table blocks in proposal content
     */
    private function extractLineItemsFromContent(array $content): array
    {
        $lineItems = [];
        
        foreach ($content as $block) {
            if ($block['type'] === 'pricing_table' && isset($block['data']['items'])) {
                foreach ($block['data']['items'] as $item) {
                    $lineItems[] = [
                        'unit_price' => $item['unit_price'] ?? 0,
                        'quantity' => $item['quantity'] ?? 1,
                        'item_discount_type' => $item['item_discount_type'] ?? 'none',
                        'item_discount_value' => $item['item_discount_value'] ?? 0,
                    ];
                }
            }
        }
        
        return $lineItems;
    }

    public function recalculateExistingProposal(int $proposalId): Proposal
    {
        try {
            $proposal = $this->getProposalById($proposalId);
            if (!$proposal) {
                throw new Exception('Proposal not found');
            }

            return $this->updateProposalTotals($proposal);
        } catch (Exception $e) {
            throw new Exception('Failed to recalculate existing proposal: '.$e->getMessage());
        }
    }

    public function getProposalById(int $id): ?Proposal
    {
        return Proposal::with([
            'account', 
            'accountContact',
            'user',
            'workspace', 
            'template', 
            'proposalStatus',
            'items.product' => function ($query) {
                $query->select(['id', 'name', 'unit_price', 'billing_type', 'billing_frequency']);
            }
        ])->find($id);
    }

    public function getProposalsByWorkspace(int $workspaceId): Collection
    {
        return Proposal::where('workspace_id', $workspaceId)
            ->with([
                'account',
                'proposalStatus',
                'items.product' => function ($query) {
                    $query->select(['id', 'name', 'unit_price', 'billing_type', 'billing_frequency']);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFilteredProposals(int $workspaceId, ?string $status = null, ?string $search = null): array
    {
        $query = Proposal::query()->where('workspace_id', $workspaceId);

        if ($status && $status !== 'all') {
            $query->where('proposal_status_id', $status);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $proposals = $query->with([
            'account',
            'accountContact',
            'user',
            'proposalStatus',
            'items.product' => function ($query) {
                $query->select(['id', 'name', 'unit_price', 'billing_type', 'billing_frequency']);
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return [
            'proposals' => $proposals,
        ];
    }

    private function getDraftStatusId(int $workspaceId): ?int
    {
        $draftStatus = ProposalStatus::where('workspace_id', $workspaceId)
            ->where('is_system', true)
            ->where('title', 'Draft')
            ->first();
        
        return $draftStatus?->id;
    }

    public function getProposalsByAccount(int $accountId): Collection
    {
        return Proposal::where('account_id', $accountId)
            ->with([
                'items.product' => function ($query) {
                    $query->select(['id', 'name', 'unit_price', 'billing_type', 'billing_frequency']);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateBlocks(Proposal $proposal, array $blocks): Proposal
    {
        try {
            $proposal->update(['blocks' => $blocks]);

            return $proposal->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update proposal blocks: '.$e->getMessage());
        }
    }

    public function addBlock(Proposal $proposal, array $block): Proposal
    {
        try {
            $blocks = $proposal->blocks ?? [];
            $blocks[] = $block;

            return $this->updateBlocks($proposal, $blocks);
        } catch (Exception $e) {
            throw new Exception('Failed to add block: '.$e->getMessage());
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
        } catch (Exception $e) {
            throw new Exception('Failed to update block: '.$e->getMessage());
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
        } catch (Exception $e) {
            throw new Exception('Failed to delete block: '.$e->getMessage());
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
        } catch (Exception $e) {
            throw new Exception('Failed to reorder blocks: '.$e->getMessage());
        }
    }
}
