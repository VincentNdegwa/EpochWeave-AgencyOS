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
import type { ProposalStatusModel } from '@/types/models/proposal';

export function createColumns(
    onEdit?: (status: ProposalStatusModel) => void,
    onDelete?: (status: ProposalStatusModel) => void,
): ColumnDef<ProposalStatusModel>[] {
    return [
        {
            accessorKey: 'title',
            header: 'Status Name',
            cell: ({ row }) => {
                const status = row.original;

                return h('span', {}, status.title);
            },
        },
        {
            accessorKey: 'color',
            header: 'Color',
            cell: ({ row }) => {
                const color = row.getValue('color') as string;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h('div', {
                        class: 'w-4 h-4 rounded border',
                        style: { backgroundColor: color },
                    }),
                    h('span', { class: 'text-sm font-mono' }, color),
                ]);
            },
        },
        {
            accessorKey: 'is_system',
            header: 'Type',
            cell: ({ row }) => {
                const isSystem = row.getValue('is_system') as boolean;
                const variant = isSystem ? 'secondary' : 'default';
                const label = isSystem ? 'System' : 'Custom';

                return h(Badge, { variant }, () => label);
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const status = row.original;

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
                            h(
                                DropdownMenuItem,
                                {
                                    onClick: () => onEdit?.(status),
                                    disabled: status.is_system,
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
                                                title: 'Delete Proposal Status',
                                                description:
                                                    'Are you sure you want to delete this status? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(status);
                                        }
                                    },
                                    disabled: status.is_system,
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
