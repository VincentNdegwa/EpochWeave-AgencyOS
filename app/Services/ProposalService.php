<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\ProposalStatus;
use Exception;
use Illuminate\Support\Collection;

class ProposalService
{
    public function __construct(private ActivityService $activityService) {}

    public function createProposalWithItems(array $data, array $items = []): Proposal
    {
        try {
            if (! isset($data['proposal_status_id'])) {
                $data['proposal_status_id'] = $this->getDraftStatusId($data['workspace_id']);
            }

            $proposal = Proposal::create($data);
            $this->activityService->created($proposal);

            if (! empty($items)) {
                $this->createProposalItems($proposal, $items);
                $this->updateProposalTotals($proposal);
            }

            return $proposal->fresh(['items.product', 'proposalStatus', 'accountContact', 'user']);
        } catch (Exception $e) {
            throw new Exception('Failed to create proposal with items: '.$e->getMessage());
        }
    }

    public function createProposalItems(Proposal $proposal, array $items): void
    {
        try {
            $proposalItems = [];
            foreach ($items as $index => $item) {
                $financials = $this->computeItemFinancials($item);

                $proposalItems[] = [
                    'proposal_id' => $proposal->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['description'] ?? $item['item_name'] ?? '',
                    'description' => $item['item_description'] ?? $item['description'] ?? null,
                    'unit_label' => $item['unit'] ?? 'Pcs',
                    'billing_type' => $item['billing_type'] ?? 'one_time',
                    'billing_frequency' => $item['billing_frequency'] ?? 'none',
                    'quantity' => $financials['quantity'],
                    'unit_price' => $financials['unit_price'],
                    'subtotal' => $financials['subtotal'],
                    'discount_type' => $financials['discount_type'],
                    'discount_value' => $financials['discount_value'],
                    'discount_amount' => $financials['discount_amount'],
                    'tax_type' => $financials['tax_type'],
                    'tax_value' => $financials['tax_value'],
                    'total_tax_amount' => $financials['tax_amount'],
                    'total' => $financials['total'],
                    'is_optional' => $item['is_optional'] ?? false,
                    'is_selected' => $item['is_selected'] ?? true,
                    'position' => $item['position'] ?? $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($proposalItems)) {
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
            $this->activityService->updated($proposal);

            $proposal->items()->delete();
            $this->createProposalItems($proposal, $items);
            $this->updateProposalTotals($proposal);

            return $proposal->fresh(['items.product']);
        } catch (Exception $e) {
            throw new Exception('Failed to update proposal: '.$e->getMessage());
        }
    }

    public function deleteProposal(Proposal $proposal): void
    {
        try {
            $this->activityService->deleted($proposal);
            $proposal->delete();
        } catch (Exception $e) {
            throw new Exception('Failed to delete proposal: '.$e->getMessage());
        }
    }

    public function updateProposalTotals(Proposal $proposal): Proposal
    {
        try {
            $items = $proposal->items()->get();
            $proposal->setRelation('items', $items);

            \Log::info('Proposal Items', [
                'data' => $items,
            ]);

            $subtotal = $items->sum('subtotal');
            $discountTotal = $items->sum('discount_amount');
            $taxAmount = $items->sum('total_tax_amount');
            $grandTotal = $items->sum('total');

            \Log::info('Proposal data', [
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'total_tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
            ]);

            $proposal->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'total_tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
            ]);

            return $proposal->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update proposal totals: '.$e->getMessage());
        }
    }

    private function computeItemFinancials(array $item): array
    {
        $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0.0;
        $quantity = isset($item['quantity']) ? (float) $item['quantity'] : 1.0;

        $itemSubtotal = $unitPrice * $quantity;

        $discountType = $item['discount_type'] ?? 'none';
        $discountValue = isset($item['discount_value']) ? (float) $item['discount_value'] : 0.0;

        if ($discountType === 'percentage' && $discountValue > 0) {
            $discountAmount = $itemSubtotal * ($discountValue / 100);
        } elseif ($discountType === 'fixed' && $discountValue > 0) {
            $discountAmount = $discountValue;
        } else {
            $discountAmount = 0.0;
        }

        $subtotalAfterDiscount = $itemSubtotal - $discountAmount;

        $taxType = $item['tax_type'] ?? 'none';
        $taxValue = isset($item['tax_value']) ? (float) $item['tax_value'] : 0.0;

        if ($taxType === 'percentage' && $taxValue > 0) {
            $taxAmount = $subtotalAfterDiscount * ($taxValue / 100);
        } elseif ($taxType === 'fixed' && $taxValue > 0) {
            $taxAmount = $taxValue;
        } else {
            $taxAmount = 0.0;
        }

        return [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $itemSubtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'tax_type' => $taxType,
            'tax_value' => $taxValue,
            'tax_amount' => $taxAmount,
            'total' => $subtotalAfterDiscount + $taxAmount,
        ];
    }

    public function recalculateExistingProposal(int $proposalId): Proposal
    {
        try {
            $proposal = $this->getProposalById($proposalId);
            if (! $proposal) {
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
            },
            'comments' => fn ($query) => $query->latest()->limit(50),
            'comments.user:id,name',
            'notes' => fn ($query) => $query->latest()->limit(50),
            'notes.user:id,name',
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
                },
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
            },
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
                },
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

    public function duplicateProposal(Proposal $proposal, int $draftStatusId, string $newProposalNumber): Proposal
    {
        try {
            return \DB::transaction(function () use ($proposal, $draftStatusId, $newProposalNumber) {
                $cloneData = $proposal->replicate([
                    'proposal_number',
                    'token',
                    'sent_at',
                    'viewed_at',
                    'last_viewed_at',
                    'view_count',
                    'decided_at',
                    'accepted_at',
                    'signed_at',
                    'expired_at',
                    'decline_reason',
                    'deposit_invoice_id',
                    'project_id',
                    'created_at',
                    'updated_at',
                ])->toArray();

                $cloneData['proposal_number'] = $newProposalNumber;
                $cloneData['proposal_status_id'] = $draftStatusId;
                $cloneData['token'] = \Str::uuid();
                $cloneData['sent_at'] = null;
                $cloneData['viewed_at'] = null;
                $cloneData['last_viewed_at'] = null;
                $cloneData['view_count'] = 0;
                $cloneData['decided_at'] = null;
                $cloneData['accepted_at'] = null;
                $cloneData['signed_at'] = null;
                $cloneData['expired_at'] = null;
                $cloneData['decline_reason'] = null;
                $cloneData['deposit_invoice_id'] = null;
                $cloneData['project_id'] = null;

                $newProposal = Proposal::create($cloneData);

                foreach ($proposal->items as $item) {
                    $itemData = $item->replicate(['proposal_id', 'created_at', 'updated_at'])->toArray();
                    $itemData['proposal_id'] = $newProposal->id;
                    ProposalItem::create($itemData);
                }

                $this->activityService->record($newProposal, 'proposal.duplicated', 'Proposal duplicated from '.$proposal->proposal_number.'.');

                return $newProposal->fresh(['items.product', 'proposalStatus', 'accountContact', 'user']);
            });
        } catch (\Throwable $e) {
            throw new Exception('Failed to duplicate proposal: '.$e->getMessage());
        }
    }
}
