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
import type { Proposal } from '@/types/models/proposal';

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
      accessorKey: 'total_amount',
      header: 'Value',
      cell: ({ row }) => {
        const amount = row.getValue('total_amount') as number;
        const currency = row.original.currency;
        return h('span', {}, formatCurrency(amount, { currency }));
      },
    },
    {
      accessorKey: 'status',
      header: 'Status',
      cell: ({ row }) => {
        const status = row.getValue('status') as string;
        const statusMap: Record<string, { label: string; variant: 'default' | 'secondary' | 'outline' | 'destructive' }> = {
          draft: { label: 'Draft', variant: 'secondary' },
          sent: { label: 'Sent', variant: 'outline' },
          accepted: { label: 'Accepted', variant: 'default' },
          rejected: { label: 'Rejected', variant: 'destructive' },
          expired: { label: 'Expired', variant: 'secondary' },
        };
        const map = statusMap[status] ?? { label: status, variant: 'secondary' };
        return h(Badge, { variant: map.variant }, () => map.label);
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
