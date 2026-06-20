import { Link } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { useCurrency } from '@/composables/useCurrency';
import type { Invoice } from '@/types/models/invoice';
import InvoiceActions from '../components/InvoiceActions.vue';

export function createColumns(): ColumnDef<Invoice>[] {
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
            accessorKey: 'invoice_number',
            header: 'Invoice #',
            cell: ({ row }) => {
                const invoice = row.original;
                const number = invoice.invoice_number ?? `INV-${invoice.id}`;

                return h(
                    Link,
                    {
                        href: `/invoices/${invoice.id}`,
                        class: 'font-mono text-xs font-medium hover:underline',
                    },
                    () => `#${number}`,
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
            header: 'Total',
            cell: ({ row }) => {
                const amount = row.getValue('grand_total') as number;
                const currency = row.original.currency;

                return h('span', {}, formatCurrency(amount, { currency }));
            },
        },
        {
            accessorKey: 'invoice_status',
            header: 'Status',
            cell: ({ row }) => {
                const status = row.original.invoice_status;

                if (!status) {
                    return h(Badge, { variant: 'secondary' }, () => 'Unknown');
                }

                return h(
                    Badge,
                    {
                        style: {
                            backgroundColor: status.color || '#6b7280',
                            color: 'white',
                        },
                    },
                    () => status.title,
                );
            },
        },
        {
            accessorKey: 'due_date',
            header: 'Due Date',
            cell: ({ row }) => {
                const value = row.getValue('due_date') as string | null;

                if (!value) {
                    return h('span', { class: 'text-muted-foreground' }, '—');
                }

                return h(
                    'span',
                    { class: 'text-sm text-muted-foreground' },
                    new Date(value).toLocaleDateString(),
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
                const invoice = row.original;

                return h(InvoiceActions, { invoice, variant: 'dropdown', size: 'icon' });
            },
        },
    ];
}
