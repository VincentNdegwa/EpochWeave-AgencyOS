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
import type { EnumOption } from '@/composables/useEnums';
import { show as accountShow } from '@/routes/accounts';
import type { Account } from '@/types/models/account';

export function createColumns(
    accountStatuses: Record<string, EnumOption>,
    onDelete?: (account: Account) => void,
    formatCurrency?: (value: number) => string,
    onEdit?: (account: Account) => void,
): ColumnDef<Account>[] {
    return [
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
                                    { href: accountShow(account.id).url },
                                    () => 'View',
                                ),
                            ),
                            h(
                                DropdownMenuItem,
                                {
                                    onClick: () => onEdit?.(account),
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
                                                title: 'Delete Account',
                                                description:
                                                    'Are you sure you want to delete this account? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(account);
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
