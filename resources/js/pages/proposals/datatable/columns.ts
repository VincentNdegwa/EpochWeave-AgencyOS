import { Link } from '@inertiajs/vue3';
import { MoreHorizontal } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCurrency } from '@/composables/useCurrency';
import { show as proposalShow } from '@/routes/proposals';
import type { Proposal, ProposalStatusModel } from '@/types/models/proposal';

export function createColumns(
    onEdit?: (proposal: Proposal) => void,
    onDelete?: (proposal: Proposal) => void,
): ColumnDef<Proposal>[] {
    const { format: formatCurrency } = useCurrency();

    return [
        {
            id: 'select',
            header: ({ table }) =>
                h(Checkbox, {
                    modelValue: table.getIsAllPageRowsSelected(),
                    'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                        table.toggleAllPageRowsSelected(value as boolean),
                    'aria-label': 'Select all',
                }),
            cell: ({ row }) =>
                h(Checkbox, {
                    modelValue: row.getIsSelected(),
                    'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                        row.toggleSelected(value as boolean),
                    'aria-label': 'Select row',
                }),
            enableSorting: false,
            enableHiding: false,
        },
        {
            accessorKey: 'title',
            header: 'Title',
            cell: ({ row }) => {
                const proposal = row.original;

                return h(
                    Link,
                    {
                        href: proposalShow(proposal.id).url,
                        class: 'font-medium hover:underline',
                    },
                    () => proposal.title,
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
                const proposalStatus =
                    proposal.proposal_status as ProposalStatusModel;

                if (!proposalStatus) {
                    return h(Badge, { variant: 'secondary' }, () => 'Unknown');
                }

                return h(
                    Badge,
                    {
                        style: {
                            backgroundColor: proposalStatus.color,
                            color: 'white',
                        },
                    },
                    () => proposalStatus.title,
                );
            },
        },
        {
            accessorKey: 'updated_at',
            header: 'Last updated',
            cell: ({ row }) => {
                const value = row.getValue('updated_at') as string;

                return h(
                    'span',
                    { class: 'text-sm text-muted-foreground' },
                    new Date(value).toLocaleDateString(),
                );
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const proposal = row.original;

                return h('div', { class: 'relative' }, [
                    h(DropdownMenu, {}, () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(
                                Button,
                                { variant: 'ghost', class: 'w-8 h-8 p-0' },
                                () => [
                                    h(
                                        'span',
                                        { class: 'sr-only' },
                                        'Open menu',
                                    ),
                                    h(MoreHorizontal, { class: 'w-4 h-4' }),
                                ],
                            ),
                        ),
                        h(DropdownMenuContent, { align: 'end' }, () => [
                            h(DropdownMenuItem, { asChild: true }, () =>
                                h(
                                    Link,
                                    { href: proposalShow(proposal.id).url },
                                    () => 'View',
                                ),
                            ),
                            h(
                                DropdownMenuItem,
                                {
                                    onClick: () => onEdit?.(proposal),
                                },
                                () => 'Edit',
                            ),
                            h(DropdownMenuSeparator),
                            h(
                                DropdownMenuItem,
                                {
                                    class: 'text-destructive',
                                    onClick: async () => {
                                        const { confirm } =
                                            await import('@/composables/useConfirmation');

                                        if (
                                            await confirm({
                                                title: 'Delete Proposal',
                                                description:
                                                    'Are you sure you want to delete this proposal? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(proposal);
                                        }
                                    },
                                },
                                () => 'Delete',
                            ),
                        ]),
                    ]),
                ]);
            },
        },
    ];
}
