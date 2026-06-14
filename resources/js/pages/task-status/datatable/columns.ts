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
import type { TaskStatus } from '@/types/models/task_status';

export function createColumns(
    onEdit?: (status: TaskStatus) => void,
    onDelete?: (status: TaskStatus) => void,
): ColumnDef<TaskStatus>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Name',
            cell: ({ row }) => {
                const status = row.original;
                return h('div', { class: 'flex items-center gap-2' }, [
                    h('span', {
                        class: 'h-2.5 w-2.5 shrink-0 rounded-full',
                        style: { backgroundColor: status.color || '#94a3b8' },
                    }),
                    h('span', { class: 'font-medium' }, status.name),
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
            accessorKey: 'is_default',
            header: 'Default',
            cell: ({ row }) => {
                const value = row.getValue('is_default') as boolean;
                return h(Badge, { variant: value ? 'default' : 'outline' }, () => (value ? 'Yes' : 'No'));
            },
        },
        {
            accessorKey: 'is_closed',
            header: 'Closed',
            cell: ({ row }) => {
                const value = row.getValue('is_closed') as boolean;
                return h(Badge, { variant: value ? 'default' : 'outline' }, () => (value ? 'Yes' : 'No'));
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
                                                description: 'Are you sure you want to delete this task status? This action cannot be undone.',
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
