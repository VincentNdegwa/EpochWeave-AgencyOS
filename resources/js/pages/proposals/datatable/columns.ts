import { Link, router } from '@inertiajs/vue3';
import { MoreHorizontal } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCurrency } from '@/composables/useCurrency';
import ProposalController from '@/actions/App/Http/Controllers/ProposalController';
import { edit as proposalEdit } from '@/routes/proposals';
import type { Proposal, ProposalStatusModel } from '@/types/models/proposal';

export function createColumns(onDelete: (proposal: Proposal) => void): ColumnDef<Proposal>[] {
  const { format: formatCurrency } = useCurrency();

  return [
    {
      accessorKey: 'title',
      header: 'Title',
      cell: ({ row }) => {
        const proposal = row.original;
        return h(
          Link,
          {
            href: proposalEdit(proposal.id).url,
            class: 'font-medium hover:underline',
          },
          () => proposal.title
        );
      },
    },
    {
      accessorKey: 'account',
      header: 'Account',
      cell: ({ row }) => {
        const account = row.original.account;
        if (!account) {
          return h('span', { class: 'text-muted-foreground' }, '—');
        }
        return h('span', {}, account.company_name);
      },
    },
    {
      accessorKey: 'grand_total',
      header: 'Value',
      cell: ({ row }) => {
        const amount = row.getValue('grand_total') as number;
        const currency = row.original.currency;
        return h('span', {}, formatCurrency(amount, { currency }));
      },
    },
    {
      accessorKey: 'proposalStatus',
      header: 'Status',
      cell: ({ row }) => {
        const proposal = row.original;
        const proposalStatus = proposal.proposal_status as ProposalStatusModel;
        
        if (!proposalStatus) {
          return h(Badge, { variant: 'secondary' }, () => 'Unknown');
        }
        
        return h(Badge, { 
          style: { backgroundColor: proposalStatus.color, color: 'white' }
        }, () => proposalStatus.title);
      },
    },
    {
      accessorKey: 'updated_at',
      header: 'Last updated',
      cell: ({ row }) => {
        const value = row.getValue('updated_at') as string;
        return h('span', { class: 'text-sm text-muted-foreground' }, new Date(value).toLocaleDateString());
      },
    },
    {
      id: 'actions',
      enableHiding: false,
      cell: ({ row }) => {
        const proposal = row.original;
        return h(
          DropdownMenu,
          {},
          () => [
            h(
              DropdownMenuTrigger,
              { asChild: true },
              () =>
                h(
                  Button,
                  { variant: 'ghost', class: 'h-8 w-8 p-0' },
                  () => h(MoreHorizontal, { class: 'h-4 w-4' })
                )
            ),
            h(DropdownMenuContent, { align: 'end' }, () => [
              h(
                DropdownMenuItem,
                {
                  onClick: () => router.visit(proposalEdit(proposal.id).url),
                },
                () => 'Edit'
              ),
              h(
                DropdownMenuItem,
                {
                  onClick: () => router.visit(ProposalController.show(proposal.id).url),
                },
                () => 'View'
              ),
              h(DropdownMenuSeparator),
              h(
                DropdownMenuItem,
                {
                  class: 'text-destructive focus:text-destructive',
                  onClick: () => onDelete(proposal),
                },
                () => 'Delete'
              ),
            ]),
          ]
        );
      },
    },
  ];
}
