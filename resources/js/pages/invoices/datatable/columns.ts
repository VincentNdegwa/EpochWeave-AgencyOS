import { Link } from '@inertiajs/vue3';
import { MoreHorizontal } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCurrency } from '@/composables/useCurrency';
import { useInvoiceStatuses } from '@/composables/useEnums';
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import type { Invoice } from '@/types/models/invoice';

export function createColumns(
    onDelete?: (invoice: Invoice) => void,
): ColumnDef<Invoice>[] {
    const { format: formatCurrency } = useCurrency();
    const invoiceStatuses = useInvoiceStatuses();

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
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const status = row.getValue('status') as string;
                const config = invoiceStatuses.getByValue(status);

                return h(
                    Badge,
                    {
                        style: {
                            backgroundColor: config?.hexColor || '#6b7280',
                            color: 'white',
                        },
                    },
                    () => config?.label || status,
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
                                    { href: `/invoices/${invoice.id}` },
                                    () => 'View',
                                ),
                            ),
                            h(DropdownMenuItem, { asChild: true }, () =>
                                h(
                                    Link,
                                    { href: InvoiceController.edit(invoice.id).url },
                                    () => 'Edit',
                                ),
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
                                                title: 'Delete Invoice',
                                                description:
                                                    'Are you sure you want to delete this invoice? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(invoice);
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
