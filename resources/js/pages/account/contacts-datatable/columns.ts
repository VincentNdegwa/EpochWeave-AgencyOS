import { MoreHorizontal, Pencil, Trash2 } from '@lucide/vue';
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
import type { AccountContact } from '@/types/models/account';

export function createContactColumns(
    onEdit?: (contact: AccountContact) => void,
    onDelete?: (contact: AccountContact) => void,
): ColumnDef<AccountContact>[] {
    return [
        {
            accessorKey: 'first_name',
            header: 'Name',
            cell: ({ row }) => {
                const contact = row.original;

                return h(
                    'div',
                    { class: 'font-medium' },
                    `${contact.first_name} ${contact.last_name}`,
                );
            },
        },
        {
            accessorKey: 'email',
            header: 'Email',
            cell: ({ row }) => row.getValue('email'),
        },
        {
            accessorKey: 'phone',
            header: 'Phone',
            cell: ({ row }) => {
                const phone = row.getValue('phone') as string | null;

                return phone || '—';
            },
        },
        {
            accessorKey: 'job_title',
            header: 'Job Title',
            cell: ({ row }) => {
                const jobTitle = row.getValue('job_title') as string | null;

                return jobTitle || '—';
            },
        },
        {
            accessorKey: 'is_primary',
            header: 'Primary',
            cell: ({ row }) => {
                const isPrimary = row.getValue('is_primary') as boolean;

                return isPrimary
                    ? h(Badge, { variant: 'default' }, () => 'Yes')
                    : h('span', { class: 'text-muted-foreground' }, 'No');
            },
        },
        {
            accessorKey: 'is_verified',
            header: 'Verified',
            cell: ({ row }) => {
                const isVerified = row.getValue('is_verified') as boolean;

                return isVerified
                    ? h(Badge, { variant: 'outline' }, () => 'Yes')
                    : h('span', { class: 'text-muted-foreground' }, 'No');
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const contact = row.original;

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
                                { onClick: () => onEdit?.(contact) },
                                () => [
                                    h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                    'Edit',
                                ],
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
                                                title: 'Delete Contact',
                                                description:
                                                    'Are you sure you want to delete this contact? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(contact);
                                        }
                                    },
                                },
                                () => [
                                    h(Trash2, { class: 'mr-2 h-4 w-4' }),
                                    'Delete',
                                ],
                            ),
                        ]),
                    ]),
                ]);
            },
        },
    ];
}
