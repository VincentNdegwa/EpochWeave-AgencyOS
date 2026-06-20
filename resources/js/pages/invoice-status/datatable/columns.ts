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
import type { InvoiceStatus } from '@/types/models/invoice_status';

export function createColumns(
    onEdit?: (status: InvoiceStatus) => void,
    onDelete?: (status: InvoiceStatus) => void,
): ColumnDef<InvoiceStatus>[] {
    return [
        {
            accessorKey: 'title',
            header: 'Title',
            cell: ({ row }) => {
                const status = row.original;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h('span', {
                        class: 'h-2.5 w-2.5 shrink-0 rounded-full',
                        style: { backgroundColor: status.color || '#94a3b8' },
                    }),
                    h('span', { class: 'font-medium' }, status.title),
                ]);
            },
        },
        {
            accessorKey: 'position',
            header: 'Position',
            cell: ({ row }) => {
                const value = row.getValue('position') as number;

                return h('span', { class: 'text-sm tabular-nums text-muted-foreground' }, String(value));
            },
        },
        {
            accessorKey: 'is_system',
            header: 'System',
            cell: ({ row }) => {
                const value = row.getValue('is_system') as boolean;

                return h(Badge, { variant: value ? 'default' : 'outline' }, () => (value ? 'System' : 'Custom'));
            },
        },
        {
            accessorKey: 'automation_trigger',
            header: 'Automation',
            cell: ({ row }) => {
                const value = row.getValue('automation_trigger') as string | null;

                return h('span', { class: 'text-xs text-muted-foreground' }, value ?? '—');
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
                            h(Button, { variant: 'ghost', class: 'w-8 h-8 p-0' }, () => [
                                h('span', { class: 'sr-only' }, 'Open menu'),
                                h(MoreHorizontal, { class: 'w-4 h-4' }),
                            ]),
                        ),
                        h(DropdownMenuContent, { align: 'end' }, () => [
                            h(DropdownMenuItem, { onClick: () => onEdit?.(status) }, () => 'Edit'),
                            h(DropdownMenuSeparator),
                            h(
                                DropdownMenuItem,
                                {
                                    class: 'text-destructive',
                                    onClick: async () => {
                                        const { confirm } = await import('@/composables/useConfirmation');

                                        if (
                                            await confirm({
                                                title: 'Delete Status',
                                                description: 'Are you sure you want to delete this invoice status? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(status);
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
