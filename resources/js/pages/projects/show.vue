<script setup lang="ts">
import { Head, router, setLayoutProps } from '@inertiajs/vue3';
import { Pencil, List, Kanban, MessageSquare, Paperclip, StickyNote } from '@lucide/vue';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import { dashboard } from '@/routes';
import projects from '@/routes/projects';
import type { Project } from '@/types/models/project';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';
import ProjectFormDialog from './dialogs/ProjectFormDialog.vue';
import KanbanView from '../tasks/KanbanView.vue';
import TaskDataTable from '../tasks/datatable/data-table.vue';
import { createColumns as createTaskColumns } from '../tasks/datatable/columns';
import { useDateFormat } from '@/composables/useDateFormat';

const { formatDate } = useDateFormat();

const { project, task_statuses } = defineProps<{
    project: Project;
    task_statuses: TaskStatus[];
}>();

const editOpen = ref(false);
const activeTab = ref<'overview' | 'tasks'>('overview');
const taskViewMode = ref<'kanban' | 'list'>('kanban');

const progress = project.tasks_total > 0
    ? Math.round((project.tasks_completed / project.tasks_total) * 100)
    : 0;

const handleEditSuccess = () => {
    editOpen.value = false;
    router.reload();
};

const taskColumns = createTaskColumns(
    (task: Task) => {
        router.visit(`/tasks/${task.id}/edit`);
    },
    (task: Task) => {
        router.delete(`/tasks/${task.id}`);
    },
);

const tabs = [
    { key: 'overview' as const, label: 'Overview' },
    { key: 'tasks' as const, label: 'Tasks' },
];

const timelineItems = computed(() => {
    const items: { icon: any; title: string; count: number; color: string }[] = [];
    items.push({ icon: MessageSquare, title: 'Comments', count: 0, color: '#3b82f6' });
    items.push({ icon: StickyNote, title: 'Notes', count: 0, color: '#f59e0b' });
    items.push({ icon: Paperclip, title: 'Attachments', count: 0, color: '#10b981' });
    return items;
});

setLayoutProps({
    title: project.name,
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Projects', href: projects.index() },
        { title: project.name },
    ],
});
</script>

<template>
    <Head :title="project.name" />

    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-border pb-4">
            <div class="flex items-center gap-2.5">
                <span
                    class="h-3.5 w-3.5 rounded-full ring-2 ring-offset-2 ring-offset-background"
                    :style="{ backgroundColor: project.color || '#6366f1' }"
                />
                <h1 class="text-xl font-bold tracking-tight text-foreground">{{ project.name }}</h1>
                <Badge
                    :style="{ backgroundColor: project.status?.color || '#6b7280', color: '#fff' }"
                    class="rounded-md text-[11px] font-semibold uppercase tracking-wider"
                >
                    {{ project.status?.title }}
                </Badge>
            </div>
            <Button size="sm" variant="outline" class="gap-1.5" @click="editOpen = true">
                <Pencil class="h-3.5 w-3.5" />
                Edit Project
            </Button>
        </div>

        <div class="flex items-center justify-between">
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
            <ButtonGroup v-if="activeTab === 'tasks'" class="ml-auto">
                <Button
                    :variant="taskViewMode === 'list' ? 'default' : 'ghost'"
                    size="sm"
                    @click="taskViewMode = 'list'"
                    class="h-7 px-2"
                >
                    <List class="h-3.5 w-3.5" />
                </Button>
                <Button
                    :variant="taskViewMode === 'kanban' ? 'default' : 'ghost'"
                    size="sm"
                    @click="taskViewMode = 'kanban'"
                    class="h-7 px-2"
                >
                    <Kanban class="h-3.5 w-3.5" />
                </Button>
            </ButtonGroup>
        </div>

        <div v-if="activeTab === 'overview'" class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 space-y-4">
                    <div class="border-b p-5 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Description</h3>
                        <p class="text-sm leading-relaxed text-foreground/90 whitespace-pre-wrap">
                            {{ project.description || 'No description provided.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="border-b p-4 space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Account</h3>
                            <p class="text-sm font-semibold">{{ project.account?.company_name || 'Unassigned' }}</p>
                        </div>
                        <div class="border-b p-4 space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Progress</h3>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span>{{ progress }}%</span>
                                    <span class="text-muted-foreground">{{ project.tasks_completed }}/{{ project.tasks_total }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-muted">
                                    <div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${progress}%` }" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-b p-5 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Details</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Start Date</span>
                                <span class="font-medium">{{ formatDate(project.start_date) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Due Date</span>
                                <span class="font-medium">{{ formatDate(project.due_date) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Hourly Rate</span>
                                <span class="font-medium">${{ project.hourly_rate || '0' }}/h</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Hours Logged</span>
                                <span class="font-medium">{{ project.hours_logged ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Hours Budgeted</span>
                                <span class="font-medium">{{ project.hours_budgeted ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Portal Visible</span>
                                <span class="font-medium">{{ project.portal_visible ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-b p-5 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Team</h3>
                        <div class="flex flex-wrap gap-2">
                            <div
                                v-for="member in project.members"
                                :key="member.id"
                                class="flex items-center gap-2 rounded-lg bg-muted px-3 py-2 text-sm"
                            >
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                                    {{ member.user?.name?.charAt(0) || '?' }}
                                </div>
                                <div>
                                    <p class="font-medium">{{ member.user?.name || 'Unknown' }}</p>
                                    <p v-if="member.role" class="text-xs text-muted-foreground">{{ member.role }}</p>
                                </div>
                            </div>
                            <span v-if="!project.members?.length" class="text-sm text-muted-foreground">No team members assigned.</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="border-b p-5 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Activity Timeline</h3>
                        <div class="relative space-y-6 pl-6">
                            <div class="absolute left-0 top-2 bottom-2 w-px bg-border" />
                            <div v-for="item in timelineItems" :key="item.title" class="relative">
                                <div
                                    class="absolute -left-9 top-0.5 flex h-6 w-6 items-center justify-center rounded-full border-2 border-background"
                                    :style="{ backgroundColor: item.color }"
                                >
                                    <component :is="item.icon" class="h-3 w-3 text-white" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium">{{ item.title }}</p>
                                    <p class="text-xs text-muted-foreground">{{ item.count }} items</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="space-y-4">
            <div v-if="taskViewMode === 'kanban'" class="w-full overflow-x-auto pb-4 scrollbar-thin">
                <KanbanView :tasks="project.tasks || []" :task_statuses="task_statuses" class="min-w-[850px] xl:min-w-full" />
            </div>
            <div v-else class="w-full">
                <TaskDataTable :columns="taskColumns" :data="project.tasks || []" />
            </div>
        </div>

        <ProjectFormDialog v-model:open="editOpen" :project="project" @success="handleEditSuccess" />
    </div>
</template>