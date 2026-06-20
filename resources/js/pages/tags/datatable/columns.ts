import { MoreHorizontal } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Tag } from '@/types/models/tag';

export function createColumns(
    onEdit?: (tag: Tag) => void,
    onDelete?: (tag: Tag) => void,
): ColumnDef<Tag>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Name',
            cell: ({ row }) => {
                const tag = row.original;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h('span', {
                        class: 'h-2.5 w-2.5 shrink-0 rounded-full',
                        style: { backgroundColor: tag.color || '#94a3b8' },
                    }),
                    h('span', { class: 'font-medium' }, tag.name),
                ]);
            },
        },
        {
            accessorKey: 'color',
            header: 'Color',
            cell: ({ row }) => {
                const value = row.getValue('color') as string;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h('span', {
                        class: 'h-4 w-4 rounded border',
                        style: { backgroundColor: value },
                    }),
                    h('span', { class: 'text-xs text-muted-foreground font-mono' }, value),
                ]);
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const tag = row.original;

                return h('div', { class: 'relative' }, [
                    h(DropdownMenu, {}, () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'w-8 h-8 p-0' }, () => [
                                h('span', { class: 'sr-only' }, 'Open menu'),
                                h(MoreHorizontal, { class: 'w-4 h-4' }),
                            ]),
                        ),
                        h(DropdownMenuContent, { align: 'end' }, () => [
                            h(DropdownMenuItem, { onClick: () => onEdit?.(tag) }, () => 'Edit'),
                            h(DropdownMenuSeparator),
                            h(
                                DropdownMenuItem,
                                {
                                    class: 'text-destructive',
                                    onClick: async () => {
                                        const { confirm } = await import('@/composables/useConfirmation');

                                        if (
                                            await confirm({
                                                title: 'Delete Tag',
                                                description: 'Are you sure you want to delete this tag? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(tag);
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
