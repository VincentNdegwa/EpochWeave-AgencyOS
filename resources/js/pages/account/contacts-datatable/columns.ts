import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { AccountContact } from '@/types/models/account';
import ContactActions from '../components/ContactActions.vue';

export function createContactColumns(
    accountId: number,
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

                return h(ContactActions, {
                    contact,
                    accountId,
                });
            },
        },
    ];
}
