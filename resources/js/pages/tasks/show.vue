<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { useTaskPriorities } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import tasks from '@/routes/tasks';
import type { Task } from '@/types/models/task';
import AttachmentList from './components/AttachmentList.vue';
import CommentThread from './components/CommentThread.vue';
import SubtaskList from './components/SubtaskList.vue';
import TaskActions from './components/TaskActions.vue';
import TimeEntriesPanel from './components/TimeEntriesPanel.vue';

const { task, activities } = defineProps<{
    task: Task;
    activities: { id: number; type: string; description: string; created_at: string; user?: { id: number; name: string } | null }[];
}>();
const taskPriorities = useTaskPriorities();
const priority = taskPriorities.getByValue(task.priority);

const activeTab = ref<'overview' | 'comments' | 'attachments' | 'subtasks' | 'time' | 'activity'>('overview');

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
        <div class="sticky top-0 z-30 border-b pb-4 border-border bg-background/95 backdrop-blur-sm print:hidden">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            v-if="task.status"
                            :style="{ backgroundColor: task.status.color || '#6b7280', color: '#fff' }"
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
                        <span class="font-normal text-muted-foreground">· {{ task.project?.name || 'No project' }}</span>
                    </h1>
                </div>
                <TaskActions
                    :task="task"
                    variant="split"
                    size="sm"
                />
            </div>
        </div>

        <!-- Stats -->
        <div class="border-b border-border bg-background print:hidden">
            <div class="grid grid-cols-2 divide-x divide-border sm:grid-cols-4">
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Project</p>
                    <div class="mt-0.5 flex items-center gap-1.5">
                        <span
                            class="h-2 w-2 rounded-full"
                            :style="{ backgroundColor: task.project?.color || '#6366f1' }"
                        />
                        <p class="text-base font-bold text-foreground">{{ task.project?.name || '—' }}</p>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Assignee</p>
                    <p class="mt-0.5 text-base font-bold text-foreground">{{ task.assignee?.name || 'Unassigned' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Due Date</p>
                    <p class="mt-0.5 text-base font-bold text-foreground">{{ task.due_date || '—' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Estimated</p>
                    <p class="mt-0.5 text-xl font-bold tabular-nums text-foreground">{{ task.estimated_hours ? `${task.estimated_hours}h` : '—' }}</p>
                </div>
            </div>
        </div>

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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="border-b p-5 space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Description</h3>
                            <p class="text-sm leading-relaxed text-foreground/90 whitespace-pre-wrap">
                                {{ task.description || 'No description provided.' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="border-b p-4 space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Details</h3>
                                <div class="grid grid-cols-1 gap-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Billable</span>
                                        <span class="font-medium">{{ task.is_billable ? 'Yes' : 'No' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Status</span>
                                        <span class="font-medium">{{ task.status?.title || '—' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Priority</span>
                                        <span class="font-medium">{{ priority?.label || task.priority }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Estimated Hours</span>
                                        <span class="font-medium">{{ task.estimated_hours ? `${task.estimated_hours}h` : '—' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-b p-4 space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Tags</h3>
                                <div class="flex flex-wrap gap-1.5">
                                    <Badge
                                        v-for="tag in task.tags"
                                        :key="tag.id"
                                        :style="{ backgroundColor: tag.color || '#94a3b8', color: '#fff' }"
                                        class="text-[11px]"
                                    >
                                        {{ tag.name }}
                                    </Badge>
                                    <span v-if="!task.tags?.length" class="text-sm text-muted-foreground">No tags.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <div v-if="activeTab === 'comments'" class="space-y-4">
                <div class="border-b p-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Comments</h3>
                    <CommentThread :task-id="task.id" :comments="task.comments || []" />
                </div>
            </div>

            <!-- Attachments -->
            <div v-if="activeTab === 'attachments'" class="space-y-4">
                <div class="border-b p-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Attachments</h3>
                    <AttachmentList :task-id="task.id" :attachments="task.attachments || []" />
                </div>
            </div>

            <!-- Subtasks -->
            <div v-if="activeTab === 'subtasks'" class="space-y-4">
                <div class="border-b p-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Subtasks</h3>
                    <SubtaskList :parent-task="task" />
                </div>
            </div>

            <!-- Time Entries -->
            <div v-if="activeTab === 'time'" class="space-y-4">
                <div class="border-b p-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Time Entries</h3>
                    <TimeEntriesPanel
                        :task-id="task.id"
                        :project-id="task.project_id"
                        :time-entries="task.timeEntries || []"
                    />
                </div>
            </div>

            <!-- Activity -->
            <div v-if="activeTab === 'activity'" class="space-y-4">
                <div class="border-b p-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Activity</h3>
                    <ActivityTimeline :activities="activities" />
                </div>
            </div>
        </div>

    </div>
</template>
