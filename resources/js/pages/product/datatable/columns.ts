import { Link } from '@inertiajs/vue3';
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
import { useBillingTypes } from '@/composables/useEnums';
import { show as productShow } from '@/routes/products';
import type { Product } from '@/types/models/product';

export function createColumns(
    onEdit?: (product: Product) => void,
    onDelete?: (product: Product) => void,
): ColumnDef<Product>[] {
    const { all: billingTypes } = useBillingTypes();
    const { format: formatCurrency } = useCurrency();

    return [
        {
            accessorKey: 'name',
            header: 'Name',
            cell: ({ row }) => {
                const product = row.original;

                return h(
                    Link,
                    {
                        href: productShow(product.id).url,
                        class: 'font-medium hover:underline',
                    },
                    () => row.getValue('name'),
                );
            },
        },
        {
            accessorKey: 'sku',
            header: 'SKU',
            cell: ({ row }) => {
                const sku = row.getValue('sku') as string | null;

                if (!sku) {
return h('span', { class: 'text-muted-foreground' }, '—');
}

                return h('span', { class: 'font-mono text-sm' }, sku);
            },
        },
        {
            accessorKey: 'unit',
            header: 'Unit',
            cell: ({ row }) => {
                const product = row.original;
                const unit = product.unit;

                if (!unit) {
return h('span', { class: 'text-muted-foreground' }, '—');
}

                return h(
                    'span',
                    { class: 'text-sm' },
                    `${unit.name} (${unit.abbreviation})`,
                );
            },
        },
        {
            accessorKey: 'unit_price',
            header: 'Price',
            cell: ({ row }) => {
                const value = row.getValue('unit_price') as number;
                const formatted = formatCurrency(value);

                return h('div', { class: '' }, formatted);
            },
        },
        {
            accessorKey: 'billing_type',
            header: 'Billing Type',
            cell: ({ row }) => {
                const billingType = row.getValue('billing_type') as string;
                const typeOption = billingTypes[billingType];
                const label = typeOption?.label || billingType;
                const variant = (typeOption?.variant || 'default') as
                    | 'default'
                    | 'secondary'
                    | 'destructive'
                    | 'outline';

                return h(Badge, { variant }, () => label);
            },
        },
        {
            accessorKey: 'is_active',
            header: 'Status',
            cell: ({ row }) => {
                const isActive = row.getValue('is_active') as boolean;
                const variant = isActive ? 'default' : 'secondary';
                const label = isActive ? 'Active' : 'Inactive';

                return h(Badge, { variant }, () => label);
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const product = row.original;

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
                                    { href: productShow(product.id).url },
                                    () => 'View',
                                ),
                            ),
                            h(
                                DropdownMenuItem,
                                {
                                    onClick: () => onEdit?.(product),
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
                                                title: 'Delete Product',
                                                description:
                                                    'Are you sure you want to delete this product? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(product);
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
