<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
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

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-semibold text-foreground">{{ task.title }}</h1>
                <Badge
                    :variant="priority?.variant || 'secondary'"
                    class="rounded-md text-[11px]"
                >
                    {{ priority?.label || task.priority }}
                </Badge>
                <Badge
                    v-if="task.status"
                    :style="{ backgroundColor: task.status.color || '#6b7280', color: '#fff' }"
                    class="rounded-md text-[11px]"
                >
                    {{ task.status.title }}
                </Badge>
            </div>
            <TaskActions
                :task="task"
                variant="split"
                size="sm"
            />
        </div>

        <div class="grid grid-cols-4 gap-4">
            <div class="rounded-lg border p-4">
                <p class="text-xs text-muted-foreground">Project</p>
                <div class="mt-1 flex items-center gap-1.5">
                    <span
                        class="h-2 w-2 rounded-full"
                        :style="{ backgroundColor: task.project?.color || '#6366f1' }"
                    />
                    <p class="text-sm font-semibold">{{ task.project?.name || '—' }}</p>
                </div>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-xs text-muted-foreground">Assignee</p>
                <p class="mt-1 text-sm font-semibold">{{ task.assignee?.name || 'Unassigned' }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-xs text-muted-foreground">Due Date</p>
                <p class="mt-1 text-sm font-semibold">{{ task.due_date || '—' }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-xs text-muted-foreground">Estimated</p>
                <p class="mt-1 text-sm font-semibold">{{ task.estimated_hours ? `${task.estimated_hours}h` : '—' }}</p>
            </div>
        </div>

        <Tabs default-value="overview">
            <TabsList>
                <TabsTrigger value="overview">Overview</TabsTrigger>
                <TabsTrigger value="comments">Comments</TabsTrigger>
                <TabsTrigger value="attachments">Attachments</TabsTrigger>
                <TabsTrigger value="subtasks">Subtasks</TabsTrigger>
                <TabsTrigger value="time">Time Entries</TabsTrigger>
                <TabsTrigger value="activity">Activity</TabsTrigger>
            </TabsList>

            <TabsContent value="overview" class="space-y-4">
                <div class="rounded-lg border p-4">
                    <h3 class="text-sm font-semibold">Description</h3>
                    <p class="mt-1 text-sm text-muted-foreground">{{ task.description || 'No description.' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-lg border p-4">
                        <h3 class="text-sm font-semibold">Details</h3>
                        <div class="mt-2 space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Billable</span>
                                <span>{{ task.is_billable ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Status</span>
                                <span>{{ task.status?.title || '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Priority</span>
                                <span>{{ priority?.label || task.priority }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border p-4">
                        <h3 class="text-sm font-semibold">Tags</h3>
                        <div class="mt-2 flex flex-wrap gap-1.5">
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
            </TabsContent>

            <TabsContent value="comments">
                <div class="rounded-lg border p-4">
                    <CommentThread :task-id="task.id" :comments="task.comments || []" />
                </div>
            </TabsContent>

            <TabsContent value="attachments">
                <div class="rounded-lg border p-4">
                    <AttachmentList :task-id="task.id" :attachments="task.attachments || []" />
                </div>
            </TabsContent>

            <TabsContent value="subtasks">
                <div class="rounded-lg border p-4">
                    <SubtaskList :parent-task="task" />
                </div>
            </TabsContent>

            <TabsContent value="time">
                <div class="rounded-lg border p-4">
                    <TimeEntriesPanel
                        :task-id="task.id"
                        :project-id="task.project_id"
                        :time-entries="task.timeEntries || []"
                    />
                </div>
            </TabsContent>

            <TabsContent value="activity">
                <div class="rounded-lg border p-4">
                    <ActivityTimeline :activities="activities" />
                </div>
            </TabsContent>
        </Tabs>

    </div>
</template>
