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
import type { ProductUnit } from '@/types/models/product';

export function createColumns(
    onEdit?: (unit: ProductUnit) => void,
    onDelete?: (unit: ProductUnit) => void,
): ColumnDef<ProductUnit>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Name',
            cell: ({ row }) => row.getValue('name'),
        },
        {
            accessorKey: 'abbreviation',
            header: 'Abbreviation',
            cell: ({ row }) => {
                const abbreviation = row.getValue('abbreviation') as string;

                return h(
                    'span',
                    { class: 'font-mono text-sm bg-muted px-2 py-1 rounded' },
                    abbreviation,
                );
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const unit = row.original;

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
                                    onClick: () => onEdit?.(unit),
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
                                                title: 'Delete Product Unit',
                                                description:
                                                    'Are you sure you want to delete this product unit? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(unit);
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
