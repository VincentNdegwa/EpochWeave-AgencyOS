import { Link } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { useCurrency } from '@/composables/useCurrency';
import { useBillingFrequencies, useBillingTypes } from '@/composables/useEnums';
import { show as productShow } from '@/routes/products';
import type { Product, ProductUnit } from '@/types/models/product';
import ProductActions from '../components/ProductActions.vue';

export function createColumns(
    units?: ProductUnit[],
): ColumnDef<Product>[] {
    const { all: billingTypes } = useBillingTypes();
    const { all: billingFrequencies } = useBillingFrequencies();
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
            accessorKey: 'billing_frequency',
            header: 'Frequency',
            cell: ({ row }) => {
                const value = row.getValue('billing_frequency') as string;
                const option = billingFrequencies[value];
                const label = option?.label || value;
                const variant = (option?.variant || 'outline') as
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

                return h(ProductActions, {
                    product,
                    units,
                    variant: 'dropdown',
                    size: 'icon',
                });
            },
        },
    ];
}
