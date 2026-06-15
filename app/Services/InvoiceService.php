<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\Proposal;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    public function createInvoiceWithItems(array $data, array $items = []): Invoice
    {
        try {
            $invoice = Invoice::create($data);

            if (! empty($items)) {
                $this->createInvoiceItems($invoice, $items);
                $this->updateInvoiceTotals($invoice);
            }

            return $invoice->fresh(['items.product', 'accountContact', 'user']);
        } catch (Exception $e) {
            throw new Exception('Failed to create invoice with items: '.$e->getMessage());
        }
    }

    public function createInvoiceItems(Invoice $invoice, array $items): void
    {
        try {
            $invoiceItems = [];
            foreach ($items as $index => $item) {
                $financials = $this->computeItemFinancials($item);

                $invoiceItems[] = [
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'] ?? $item['description'] ?? '',
                    'description' => $item['description'] ?? $item['item_description'] ?? null,
                    'unit_label' => $item['unit_label'] ?? 'Pcs',
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
                    'position' => $item['position'] ?? $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($invoiceItems)) {
                InvoiceItem::insert($invoiceItems);
            }
        } catch (Exception $e) {
            throw new Exception('Failed to create invoice items: '.$e->getMessage());
        }
    }

    public function updateInvoice(Invoice $invoice, array $data, array $items = []): Invoice
    {
        try {
            $invoice->update($data);
            $invoice->items()->delete();
            $this->createInvoiceItems($invoice, $items);
            $this->updateInvoiceTotals($invoice);

            return $invoice->fresh(['items.product']);
        } catch (Exception $e) {
            throw new Exception('Failed to update invoice: '.$e->getMessage());
        }
    }

    public function deleteInvoice(Invoice $invoice): void
    {
        try {
            $invoice->delete();
        } catch (Exception $e) {
            throw new Exception('Failed to delete invoice: '.$e->getMessage());
        }
    }

    public function updateInvoiceTotals(Invoice $invoice): Invoice
    {
        try {
            $items = $invoice->items()->get();
            $invoice->setRelation('items', $items);

            $subtotal = $items->sum('subtotal');
            $discountTotal = $items->sum('discount_amount');
            $taxAmount = $items->sum('total_tax_amount');
            $grandTotal = $items->sum('total');

            $invoice->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'total_tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
            ]);

            return $invoice->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update invoice totals: '.$e->getMessage());
        }
    }

    public function createInvoiceFromProposal(Proposal $proposal, ?Project $project = null): Invoice
    {
        try {
            return DB::transaction(function () use ($proposal, $project) {
                $workspace = $proposal->workspace;

                $invoiceNumber = $this->generateInvoiceNumber($workspace->id);

                // Get draft status for this workspace
                $draftStatus = InvoiceStatus::where('workspace_id', $proposal->workspace_id)
                    ->where('automation_trigger', 'draft')
                    ->first();

                $invoiceData = [
                    'workspace_id' => $proposal->workspace_id,
                    'account_id' => $proposal->account_id,
                    'account_contact_id' => $proposal->account_contact_id,
                    'proposal_id' => $proposal->id,
                    'project_id' => $project?->id,
                    'created_by' => $proposal->created_by,
                    'user_id' => $proposal->user_id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_status_id' => $draftStatus?->id,
                    'token' => Str::uuid(),
                    'currency' => $proposal->currency ?? 'USD',
                    'issue_date' => now(),
                    'due_date' => now()->addDays(30),
                    'notes' => null,
                ];

                $invoice = Invoice::create($invoiceData);

                $proposalItems = $proposal->items()->get();
                $invoiceItems = [];

                foreach ($proposalItems as $index => $proposalItem) {
                    $invoiceItems[] = [
                        'invoice_id' => $invoice->id,
                        'product_id' => $proposalItem->product_id,
                        'item_name' => $proposalItem->item_name,
                        'description' => $proposalItem->description,
                        'unit_label' => $proposalItem->unit_label,
                        'quantity' => $proposalItem->quantity,
                        'unit_price' => $proposalItem->unit_price,
                        'subtotal' => $proposalItem->subtotal,
                        'discount_type' => $proposalItem->discount_type ?? 'percentage',
                        'discount_value' => $proposalItem->discount_value ?? 0,
                        'discount_amount' => $proposalItem->discount_amount ?? 0,
                        'tax_type' => $proposalItem->tax_type ?? 'percentage',
                        'tax_value' => $proposalItem->tax_value ?? 0,
                        'total_tax_amount' => $proposalItem->total_tax_amount ?? 0,
                        'total' => $proposalItem->total,
                        'position' => $proposalItem->position ?? $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($invoiceItems)) {
                    InvoiceItem::insert($invoiceItems);
                }

                $this->updateInvoiceTotals($invoice);

                return $invoice->fresh(['items', 'account', 'accountContact', 'user', 'proposal', 'project']);
            });
        } catch (Exception $e) {
            throw new Exception('Failed to create invoice from proposal: '.$e->getMessage());
        }
    }

    public function getInvoiceById(int $id): ?Invoice
    {
        return Invoice::with([
            'account',
            'accountContact',
            'user',
            'workspace',
            'proposal',
            'project',
            'items.product' => function ($query) {
                $query->select(['id', 'name', 'unit_price']);
            },
        ])->find($id);
    }

    public function getInvoicesByWorkspace(int $workspaceId): Collection
    {
        return Invoice::where('workspace_id', $workspaceId)
            ->with([
                'account',
                'user',
                'items.product' => function ($query) {
                    $query->select(['id', 'name', 'unit_price']);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFilteredInvoices(int $workspaceId, ?string $status = null, ?string $search = null): array
    {
        $query = Invoice::query()->where('workspace_id', $workspaceId);

        if ($status && $status !== 'all') {
            // Filter by status automation trigger via relationship
            $query->whereHas('status', function ($q) use ($status) {
                $q->where('automation_trigger', $status);
            });
        }

        if ($search) {
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        $invoices = $query->with([
            'account',
            'accountContact',
            'user',
            'items.product' => function ($query) {
                $query->select(['id', 'name', 'unit_price']);
            },
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'invoices' => $invoices,
        ];
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

    private function generateInvoiceNumber(int $workspaceId): string
    {
        $prefix = 'INV';
        $year = now()->format('Y');
        $sequence = Invoice::where('workspace_id', $workspaceId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return $prefix.$year.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
