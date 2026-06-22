import { Link } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { show as projectShow } from '@/routes/projects';
import type { Project } from '@/types/models/project';
import type { ProjectStatus } from '@/types/models/project_status';
import ProjectActions from '../components/ProjectActions.vue';

export function createColumns(projectStatuses?: ProjectStatus[]): ColumnDef<Project>[] {
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
                const status = row.original.status;

                if (!status) {
                    return h(Badge, { variant: 'secondary' }, () => '—');
                }

                return h(
                    Badge,
                    {
                        style: {
                            backgroundColor: status.color,
                            color: 'white',
                        },
                    },
                    () => status.title,
                );
            },
        },
        {
            accessorKey: 'tasks_total',
            header: 'Progress',
            cell: ({ row }) => {
                const total = row.original.tasks_total || 0;
                const completed = row.original.tasks_completed || 0;
                const pct =
                    total > 0 ? Math.round((completed / total) * 100) : 0;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h(
                        'div',
                        {
                            class: 'h-1.5 w-16 overflow-hidden rounded-full bg-muted',
                        },
                        [
                            h('div', {
                                class: 'h-full rounded-full bg-primary',
                                style: { width: `${pct}%` },
                            }),
                        ],
                    ),
                    h(
                        'span',
                        { class: 'text-xs text-muted-foreground tabular-nums' },
                        `${completed}/${total}`,
                    ),
                ]);
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const project = row.original;

                return h(ProjectActions, {
                    project,
                    project_statuses: projectStatuses ?? [],
                    variant: 'dropdown',
                    size: 'icon',
                });
            },
        },
    ];
}
