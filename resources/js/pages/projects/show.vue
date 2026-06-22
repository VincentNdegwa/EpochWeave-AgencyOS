<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { List, Kanban } from '@lucide/vue';
import { ref } from 'vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import { StatBar } from '@/components/ui/stat-bar';
import { useDateFormat } from '@/composables/useDateFormat';
import { dashboard } from '@/routes';
import projects from '@/routes/projects';
import type { Project } from '@/types/models/project';
import type { ProjectStatus } from '@/types/models/project_status';
import type { TaskStatus } from '@/types/models/task_status';
import { createColumns as createTaskColumns } from '../tasks/datatable/columns';
import TaskDataTable from '../tasks/datatable/data-table.vue';
import KanbanView from '../tasks/KanbanView.vue';
import ProjectActions from './components/ProjectActions.vue';

const { formatDate } = useDateFormat();

const { project, project_statuses, task_statuses, activities } = defineProps<{
    project: Project;
    project_statuses: ProjectStatus[];
    task_statuses: TaskStatus[];
    activities: {
        id: number;
        type: string;
        description: string;
        created_at: string;
        user?: { id: number; name: string } | null;
    }[];
}>();

const activeTab = ref<'overview' | 'tasks'>('overview');
const taskViewMode = ref<'kanban' | 'list'>('kanban');

const progress =
    project.tasks_total > 0
        ? Math.round((project.tasks_completed / project.tasks_total) * 100)
        : 0;

const taskColumns = createTaskColumns();

const tabs = [
    { key: 'overview' as const, label: 'Overview' },
    { key: 'tasks' as const, label: 'Tasks' },
];

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

    <div class="flex flex-col">
        <!-- Header -->
        <div
            class="sticky top-0 z-30 bg-background/95 pb-4 backdrop-blur-sm print:hidden"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            :style="{
                                backgroundColor:
                                    project.status?.color || '#6b7280',
                                color: '#fff',
                            }"
                            class="rounded-md text-[11px]"
                        >
                            {{ project.status?.title }}
                        </Badge>
                        <!-- <span class="inline-flex items-center gap-1 rounded-md border border-border bg-muted/50 px-2 py-0.5 font-mono text-[11px] text-muted-foreground">
                            <span
                                class="h-2.5 w-2.5 rounded-full"
                                :style="{ backgroundColor: project.color || '#6366f1' }"
                            />
                            {{ project.id }}
                        </span> -->
                    </div>
                    <h1 class="text-base font-semibold text-foreground">
                        {{ project.name }}
                        <span class="font-normal text-muted-foreground"
                            >·
                            {{
                                project.account?.company_name || 'Unassigned'
                            }}</span
                        >
                    </h1>
                </div>
                <ProjectActions :project="project" :project_statuses="project_statuses" variant="split" size="sm" />
            </div>
        </div>

        <StatBar
            :items="[
                { label: 'Progress', value: `${progress}%` },
                { label: 'Start Date', value: formatDate(project.start_date) },
                { label: 'Due Date', value: formatDate(project.due_date) },
                {
                    label: 'Hourly Rate',
                    value: `$${project.hourly_rate || '0'}`,
                },
            ]"
        />

        <div class="space-y-4 pt-4">
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
                        :variant="
                            taskViewMode === 'kanban' ? 'default' : 'ghost'
                        "
                        size="sm"
                        @click="taskViewMode = 'kanban'"
                        class="h-7 px-2"
                    >
                        <Kanban class="h-3.5 w-3.5" />
                    </Button>
                </ButtonGroup>
            </div>

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
                                    project.description ||
                                    'No description provided.'
                                }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-3 border-b p-4">
                                <h3
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Account
                                </h3>
                                <p class="text-sm font-semibold">
                                    {{
                                        project.account?.company_name ||
                                        'Unassigned'
                                    }}
                                </p>
                            </div>
                            <div class="space-y-3 border-b p-4">
                                <h3
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Progress
                                </h3>
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span>{{ progress }}%</span>
                                        <span class="text-muted-foreground"
                                            >{{ project.tasks_completed }}/{{
                                                project.tasks_total
                                            }}</span
                                        >
                                    </div>
                                    <div class="h-2 rounded-full bg-muted">
                                        <div
                                            class="h-full rounded-full bg-primary transition-all"
                                            :style="{ width: `${progress}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 border-b p-5">
                            <h3
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Details
                            </h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground"
                                        >Start Date</span
                                    >
                                    <span class="font-medium">{{
                                        formatDate(project.start_date)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground"
                                        >Due Date</span
                                    >
                                    <span class="font-medium">{{
                                        formatDate(project.due_date)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground"
                                        >Hourly Rate</span
                                    >
                                    <span class="font-medium"
                                        >${{
                                            project.hourly_rate || '0'
                                        }}/h</span
                                    >
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground"
                                        >Hours Logged</span
                                    >
                                    <span class="font-medium">{{
                                        project.hours_logged ?? '—'
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground"
                                        >Hours Budgeted</span
                                    >
                                    <span class="font-medium">{{
                                        project.hours_budgeted ?? '—'
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground"
                                        >Portal Visible</span
                                    >
                                    <span class="font-medium">{{
                                        project.portal_visible ? 'Yes' : 'No'
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 border-b p-5">
                            <h3
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Team
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <div
                                    v-for="member in project.members"
                                    :key="member.id"
                                    class="flex items-center gap-2 rounded-lg bg-muted px-3 py-2 text-sm"
                                >
                                    <div
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary"
                                    >
                                        {{
                                            member.user?.name?.charAt(0) || '?'
                                        }}
                                    </div>
                                    <div>
                                        <p class="font-medium">
                                            {{ member.user?.name || 'Unknown' }}
                                        </p>
                                        <p
                                            v-if="member.role"
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ member.role }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    v-if="!project.members?.length"
                                    class="text-sm text-muted-foreground"
                                    >No team members assigned.</span
                                >
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-4 border-b p-5">
                            <h3
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Activity Timeline
                            </h3>
                            <ActivityTimeline :activities="activities" />
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-if="taskViewMode === 'kanban'"
                    class="w-full scrollbar-thin overflow-x-auto pb-4"
                >
                    <KanbanView
                        :tasks="project.tasks || []"
                        :task_statuses="task_statuses"
                        class="min-w-[850px] xl:min-w-full"
                    />
                </div>
                <div v-else class="w-full">
                    <TaskDataTable
                        :columns="taskColumns"
                        :data="project.tasks || []"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
