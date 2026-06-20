import { Link } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { useDateFormat } from '@/composables/useDateFormat';
import { useTaskPriorities } from '@/composables/useEnums';
import { show as taskShow } from '@/routes/tasks';
import type { Tag } from '@/types/models/tag';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';
import TaskActions from '../components/TaskActions.vue';

export function createColumns(
    taskStatuses?: TaskStatus[],
    tags?: Tag[],
): ColumnDef<Task>[] {
    const taskPriorities = useTaskPriorities();
    const { formatDate } = useDateFormat();

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
            accessorKey: 'title',
            header: 'Task',
            cell: ({ row }) => {
                const task = row.original;

                return h(
                    Link,
                    {
                        href: taskShow(task.id).url,
                        class: 'font-medium hover:underline',
                    },
                    () => task.title,
                );
            },
        },
        {
            accessorKey: 'project',
            header: 'Project',
            cell: ({ row }) => {
                const project = row.original.project;

                if (!project) {
                    return h('span', { class: 'text-muted-foreground' }, '—');
                }

                return h('div', { class: 'flex items-center gap-1.5' }, [
                    h('span', {
                        class: 'h-2 w-2 rounded-full',
                        style: { backgroundColor: project.color || '#6366f1' },
                    }),
                    h('span', { class: 'text-sm' }, project.name),
                ]);
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

                return h(Badge, {
                    style: { backgroundColor: status.color || '#6b7280', color: '#fff' },
                }, () => status.title);
            },
        },
        {
            accessorKey: 'priority',
            header: 'Priority',
            cell: ({ row }) => {
                const priority = row.getValue('priority') as string;
                const config = taskPriorities.getByValue(priority);

                if (!config) {
                    return h(Badge, { variant: 'secondary' }, () => priority);
                }

                return h(Badge, {
                    variant: config.variant,
                }, () => config.label);
            },
        },
        {
            accessorKey: 'assignee',
            header: 'Assignee',
            cell: ({ row }) => {
                const assignee = row.original.assignee;

                return h('span', { class: 'text-sm text-muted-foreground' }, assignee?.name || 'Unassigned');
            },
        },
        {
            accessorKey: 'due_date',
            header: 'Due',
            cell: ({ row }) => {
                const value = row.getValue('due_date') as string | null;

                return h('span', { class: 'text-sm text-muted-foreground' }, formatDate(value));
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const task = row.original;

                return h(TaskActions, {
                    task,
                    taskStatuses,
                    tags,
                    variant: 'dropdown',
                    size: 'icon',
                });
            },
        },
    ];
}
