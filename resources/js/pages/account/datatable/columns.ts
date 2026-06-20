import { Link } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import type { EnumOption } from '@/composables/useEnums';
import { show as accountShow } from '@/routes/accounts';
import type { Account } from '@/types/models/account';
import AccountActions from '../components/AccountActions.vue';

export function createColumns(
    accountStatuses: Record<string, EnumOption>,
    formatCurrency?: (value: number) => string,
): ColumnDef<Account>[] {
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
            accessorKey: 'company_name',
            header: 'Company',
            cell: ({ row }) => {
                const account = row.original;

                return h(
                    Link,
                    {
                        href: accountShow(account.id).url,
                        class: 'font-medium hover:underline',
                    },
                    () => row.getValue('company_name'),
                );
            },
        },
        {
            accessorKey: 'website',
            header: 'Website',
            cell: ({ row }) => {
                const website = row.getValue('website') as string | null;

                if (!website) {
                    return h('span', { class: 'text-muted-foreground' }, '—');
                }

                return h(
                    'a',
                    {
                        href: website,
                        target: '_blank',
                        rel: 'noopener noreferrer',
                        class: 'text-blue-600 hover:underline',
                    },
                    website,
                );
            },
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const status = row.getValue('status') as string;
                const statusOption = accountStatuses[status];
                const label = statusOption?.label || status;
                const variant = (statusOption?.variant || 'default') as
                    | 'default'
                    | 'secondary'
                    | 'destructive'
                    | 'outline';
                const colorClass = statusOption?.color;

                return h(
                    Badge,
                    {
                        variant,
                        class: colorClass,
                    },
                    () => label,
                );
            },
        },
        {
            accessorKey: 'lifetime_value',
            header: 'Lifetime Value',
            cell: ({ row }) => {
                const value = row.getValue('lifetime_value') as number;
                const formatted = formatCurrency
                    ? formatCurrency(value)
                    : new Intl.NumberFormat('en-US', {
                          style: 'currency',
                          currency: 'USD',
                      }).format(value);

                return h('div', { class: '' }, formatted);
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const account = row.original;

                return h(AccountActions, {
                    account,
                    variant: 'dropdown',
                    size: 'icon',
                });
            },
        },
    ];
}
