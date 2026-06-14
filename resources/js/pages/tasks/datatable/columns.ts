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
import { useTaskPriorities } from '@/composables/useEnums';
import { show as taskShow } from '@/routes/tasks';
import type { Task } from '@/types/models/task';

export function createColumns(
    onEdit?: (task: Task) => void,
    onDelete?: (task: Task) => void,
): ColumnDef<Task>[] {
    const taskPriorities = useTaskPriorities();

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
                }, () => status.name);
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
                return h('span', { class: 'text-sm text-muted-foreground' }, value || '—');
            },
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const task = row.original;
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
                                h(Link, { href: taskShow(task.id).url }, () => 'View'),
                            ),
                            h(DropdownMenuItem, { onClick: () => onEdit?.(task) }, () => 'Edit'),
                            h(DropdownMenuSeparator),
                            h(
                                DropdownMenuItem,
                                {
                                    class: 'text-destructive',
                                    onClick: async () => {
                                        const { confirm } = await import('@/composables/useConfirmation');
                                        if (
                                            await confirm({
                                                title: 'Delete Task',
                                                description: 'Are you sure you want to delete this task? This action cannot be undone.',
                                                confirmText: 'Delete',
                                                cancelText: 'Cancel',
                                                variant: 'destructive',
                                            })
                                        ) {
                                            onDelete?.(task);
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
