import { MoreHorizontal, Plus } from '@lucide/vue';
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
import type { CustomFieldGroup } from '@/types/models/custom_field';

export function createColumns(
    onEdit?: (group: CustomFieldGroup) => void,
    onDelete?: (group: CustomFieldGroup) => void,
    onAddField?: (group: CustomFieldGroup) => void,
    onManageFields?: (group: CustomFieldGroup) => void,
): ColumnDef<CustomFieldGroup>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Name',
            cell: ({ row }) => {
                const group = row.original;

                return h('span', { class: 'font-medium' }, group.name);
            },
        },
        {
            accessorKey: 'applies_to',
            header: 'Applies To',
            cell: ({ row }) => {
                const value = row.getValue('applies_to') as string;

                return h(
                    'span',
                    { class: 'text-sm text-muted-foreground' },
                    value,
                );
            },
        },
        {
            accessorKey: 'definitions',
            header: 'Fields',
            cell: ({ row }) => {
                const group = row.original;
                const count = group.definitions.length;

                if (count === 0) {
                    return h(
                        'span',
                        { class: 'text-sm text-muted-foreground' },
                        'No fields',
                    );
                }

                return h('div', { class: 'flex flex-wrap items-center gap-1' }, [
                    h(
                        Badge,
                        { variant: 'outline' },
                        () => `${count} field${count === 1 ? '' : 's'}`,
                    ),
                    ...group.definitions.slice(0, 3).map((definition) =>
                        h(
                            Badge,
                            { variant: 'secondary', class: 'text-xs' },
                            () => definition.label,
                        ),
                    ),
                    group.definitions.length > 3
                        ? h(
                              Badge,
                              { variant: 'secondary', class: 'text-xs' },
                              () => `+${group.definitions.length - 3} more`,
                          )
                        : null,
                ]);
            },
        },
        {
            accessorKey: 'sort_order',
            header: 'Order',
            cell: ({ row }) => {
                const value = row.getValue('sort_order') as number;

                return h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    String(value),
                );
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const group = row.original;

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
                                { onClick: () => onManageFields?.(group) },
                                () => 'Manage Fields',
                            ),
                            h(
                                DropdownMenuItem,
                                { onClick: () => onAddField?.(group) },
                                () => [
                                    h(Plus, { class: 'mr-2 h-4 w-4' }),
                                    'Add Field',
                                ],
                            ),
                            h(DropdownMenuSeparator),
                            h(
                                DropdownMenuItem,
                                { onClick: () => onEdit?.(group) },
                                () => 'Edit Group',
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
                                                title: 'Delete Group',
                                                description:
                                                    'Are you sure you want to delete this field group and all its fields? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(group);
                                        }
                                    },
                                },
                                () => 'Delete Group',
                            ),
                        ]),
                    ]),
                ]);
            },
        },
    ];
}
