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
import { useProjectStatuses } from '@/composables/useEnums';
import { show as projectShow } from '@/routes/projects';
import type { Project } from '@/types/models/project';

export function createColumns(
    onEdit?: (project: Project) => void,
    onDelete?: (project: Project) => void,
): ColumnDef<Project>[] {
    const projectStatuses = useProjectStatuses();

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
            header: 'Project',
            cell: ({ row }) => {
                const project = row.original;
                return h('div', { class: 'flex items-center gap-2' }, [
                    h('span', {
                        class: 'h-2.5 w-2.5 shrink-0 rounded-full',
                        style: { backgroundColor: project.color || '#6366f1' },
                    }),
                    h(
                        Link,
                        {
                            href: projectShow(project.id).url,
                            class: 'font-medium hover:underline',
                        },
                        () => project.name,
                    ),
                ]);
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
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const status = row.getValue('status') as string;
                const config = projectStatuses.getByValue(status);
                if (!config) {
                    return h(Badge, { variant: 'secondary' }, () => status);
                }
                return h(
                    Badge,
                    {
                        style: { backgroundColor: config.hexColor, color: 'white' },
                    },
                    () => config.label,
                );
            },
        },
        {
            accessorKey: 'tasks_total',
            header: 'Progress',
            cell: ({ row }) => {
                const total = row.original.tasks_total || 0;
                const completed = row.original.tasks_completed || 0;
                const pct = total > 0 ? Math.round((completed / total) * 100) : 0;
                return h('div', { class: 'flex items-center gap-2' }, [
                    h('div', { class: 'h-1.5 w-16 overflow-hidden rounded-full bg-muted' }, [
                        h('div', {
                            class: 'h-full rounded-full bg-primary',
                            style: { width: `${pct}%` },
                        }),
                    ]),
                    h('span', { class: 'text-xs text-muted-foreground tabular-nums' }, `${completed}/${total}`),
                ]);
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const project = row.original;
                return h('div', { class: 'relative' }, [
                    h(DropdownMenu, {}, () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'w-8 h-8 p-0' }, () => [
                                h('span', { class: 'sr-only' }, 'Open menu'),
                                h(MoreHorizontal, { class: 'w-4 h-4' }),
                            ]),
                        ),
                        h(DropdownMenuContent, { align: 'end' }, () => [
                            h(DropdownMenuItem, { asChild: true }, () =>
                                h(Link, { href: projectShow(project.id).url }, () => 'View'),
                            ),
                            h(DropdownMenuItem, { onClick: () => onEdit?.(project) }, () => 'Edit'),
                            h(DropdownMenuSeparator),
                            h(
                                DropdownMenuItem,
                                {
                                    class: 'text-destructive',
                                    onClick: async () => {
                                        const { confirm } = await import('@/composables/useConfirmation');
                                        if (
                                            await confirm({
                                                title: 'Delete Project',
                                                description: 'Are you sure you want to delete this project? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(project);
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
