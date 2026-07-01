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
import type { CompanySize } from '@/types/models/company_size';

export function createColumns(
    onEdit?: (size: CompanySize) => void,
    onDelete?: (size: CompanySize) => void,
): ColumnDef<CompanySize>[] {
    return [
        {
            accessorKey: 'label',
            header: 'Label',
            cell: ({ row }) => {
                const size = row.original;

                return h('span', { class: 'font-medium' }, size.label);
            },
        },
        {
            accessorKey: 'min_employees',
            header: 'Min Employees',
            cell: ({ row }) => {
                const value = row.getValue('min_employees') as number | null;

                return h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    value === null ? '—' : String(value),
                );
            },
        },
        {
            accessorKey: 'max_employees',
            header: 'Max Employees',
            cell: ({ row }) => {
                const value = row.getValue('max_employees') as number | null;

                return h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    value === null ? '—' : String(value),
                );
            },
        },
        {
            accessorKey: 'is_default',
            header: 'Default',
            cell: ({ row }) => {
                const value = row.getValue('is_default') as boolean;

                return h(
                    Badge,
                    { variant: value ? 'default' : 'outline' },
                    () => (value ? 'Default' : 'Custom'),
                );
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const size = row.original;

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
                                { onClick: () => onEdit?.(size) },
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
                                                title: 'Delete Company Size',
                                                description:
                                                    'Are you sure you want to delete this company size? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(size);
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
