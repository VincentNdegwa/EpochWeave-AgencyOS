<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import CommentThread from '@/components/CommentThread.vue';
import { Badge } from '@/components/ui/badge';
import { StatBar } from '@/components/ui/stat-bar';
import { useDateFormat } from '@/composables/useDateFormat';
import { useTaskPriorities } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import tasks from '@/routes/tasks';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';
import AttachmentList from './components/AttachmentList.vue';
import SubtaskList from './components/SubtaskList.vue';
import TaskActions from './components/TaskActions.vue';
import TimeEntriesPanel from './components/TimeEntriesPanel.vue';

const { task, activities, task_statuses } = defineProps<{
    task: Task;
    activities: {
        id: number;
        type: string;
        description: string;
        created_at: string;
        user?: { id: number; name: string } | null;
    }[];
    task_statuses: TaskStatus[];
}>();
const { formatDate } = useDateFormat();
const taskPriorities = useTaskPriorities();
const priority = taskPriorities.getByValue(task.priority);

const activeTab = ref<
    'overview' | 'comments' | 'attachments' | 'subtasks' | 'time' | 'activity'
>('overview');

const tabs = [
    { key: 'overview' as const, label: 'Overview' },
    { key: 'comments' as const, label: 'Comments' },
    { key: 'attachments' as const, label: 'Attachments' },
    { key: 'subtasks' as const, label: 'Subtasks' },
    { key: 'time' as const, label: 'Time Entries' },
    { key: 'activity' as const, label: 'Activity' },
];

setLayoutProps({
    title: task.title,
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Tasks', href: tasks.index() },
        { title: task.title },
    ],
});
</script>

<template>
    <Head :title="task.title" />

    <div class="flex flex-col">
        <!-- Header -->
        <div
            class="sticky top-0 z-30 bg-background/95 pb-4 backdrop-blur-sm print:hidden"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            v-if="task.status"
                            :style="{
                                backgroundColor: task.status.color || '#6b7280',
                                color: '#fff',
                            }"
                            class="rounded-md text-[11px]"
                        >
                            {{ task.status.title }}
                        </Badge>
                        <Badge
                            :variant="priority?.variant || 'secondary'"
                            class="rounded-md text-[11px]"
                        >
                            {{ priority?.label || task.priority }}
                        </Badge>
                    </div>
                    <h1 class="text-base font-semibold text-foreground">
                        {{ task.title }}
                        <span class="font-normal text-muted-foreground"
                            >· {{ task.project?.name || 'No project' }}</span
                        >
                    </h1>
                </div>
                <TaskActions
                    :task="task"
                    :task-statuses="task_statuses"
                    variant="split"
                    size="sm"
                />
            </div>
        </div>

        <StatBar
            :items="[
                {
                    label: 'Project',
                    value: task.project?.name || '—',
                    color: task.project?.color || '#6366f1',
                },
                {
                    label: 'Assignee',
                    value: task.assignee?.name || 'Unassigned',
                },
                { label: 'Due Date', value: formatDate(task.due_date) },
                {
                    label: 'Estimated',
                    value: task.estimated_hours
                        ? `${task.estimated_hours}h`
                        : '—',
                },
            ]"
        />

        <div class="space-y-4 pt-4">
            <div class="flex border-b">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    :class="[
                        '-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                        activeTab === tab.key
                            ? 'border-primary text-foreground'
                            : 'border-transparent text-muted-foreground hover:text-foreground',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Overview -->
            <div v-if="activeTab === 'overview'" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="space-y-4 border-b p-5">
                            <h3
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Description
                            </h3>
                            <p
                                class="text-sm leading-relaxed whitespace-pre-wrap text-foreground/90"
                            >
                                {{
                                    task.description ||
                                    'No description provided.'
                                }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-3 border-b p-4">
                                <h3
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Details
                                </h3>
                                <div class="grid grid-cols-1 gap-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground"
                                            >Billable</span
                                        >
                                        <span class="font-medium">{{
                                            task.is_billable ? 'Yes' : 'No'
                                        }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground"
                                            >Status</span
                                        >
                                        <span class="font-medium">{{
                                            task.status?.title || '—'
                                        }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground"
                                            >Priority</span
                                        >
                                        <span class="font-medium">{{
                                            priority?.label || task.priority
                                        }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground"
                                            >Estimated Hours</span
                                        >
                                        <span class="font-medium">{{
                                            task.estimated_hours
                                                ? `${task.estimated_hours}h`
                                                : '—'
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3 border-b p-4">
                                <h3
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Tags
                                </h3>
                                <div class="flex flex-wrap gap-1.5">
                                    <Badge
                                        v-for="tag in task.tags"
                                        :key="tag.id"
                                        :style="{
                                            backgroundColor:
                                                tag.color || '#94a3b8',
                                            color: '#fff',
                                        }"
                                        class="text-[11px]"
                                    >
                                        {{ tag.name }}
                                    </Badge>
                                    <span
                                        v-if="!task.tags?.length"
                                        class="text-sm text-muted-foreground"
                                        >No tags.</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <div v-if="activeTab === 'comments'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Comments
                    </h3>
                    <CommentThread
                        commentable-type="task"
                        :commentable-id="task.id"
                        :comments="task.comments || []"
                    />
                </div>
            </div>

            <!-- Attachments -->
            <div v-if="activeTab === 'attachments'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Attachments
                    </h3>
                    <AttachmentList
                        :task-id="task.id"
                        :attachments="task.attachments || []"
                    />
                </div>
            </div>

            <!-- Subtasks -->
            <div v-if="activeTab === 'subtasks'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Subtasks
                    </h3>
                    <SubtaskList :parent-task="task" />
                </div>
            </div>

            <!-- Time Entries -->
            <div v-if="activeTab === 'time'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Time Entries
                    </h3>
                    <TimeEntriesPanel
                        :task-id="task.id"
                        :project-id="task.project_id"
                        :time-entries="task.time_entries || []"
                    />
                </div>
            </div>

            <!-- Activity -->
            <div v-if="activeTab === 'activity'" class="space-y-4">
                <div class="space-y-4 border-b p-5">
                    <h3
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Activity
                    </h3>
                    <ActivityTimeline :activities="activities" />
                </div>
            </div>
        </div>
    </div>
</template>
