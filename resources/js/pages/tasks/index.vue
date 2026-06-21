<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, List, Kanban, Tag, ListFilter } from '@lucide/vue';
import { ref, computed } from 'vue';
import TagController from '@/actions/App/Http/Controllers/TagController';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { dashboard } from '@/routes';
import type { Tag as TagType } from '@/types/models/tag';
import type { Task } from '@/types/models/task';
import type { TaskStatus } from '@/types/models/task_status';
import TagFormDialog from '../tags/dialogs/TagFormDialog.vue';
import TaskStatusFormDialog from '../task-status/dialogs/TaskStatusFormDialog.vue';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import TaskFormDialog from './dialogs/TaskFormDialog.vue';
import KanbanView from './KanbanView.vue';

const { tasks, task_statuses, tags, display_mode, filters } = defineProps<{
    tasks: Task[];
    task_statuses: TaskStatus[];
    tags: TagType[];
    display_mode: string;
    filters?: {
        status?: string;
        search?: string;
        project_id?: string;
    };
}>();

const dialogOpen = ref(false);
const editingTask = ref<Task | null>(null);
const statusDialogOpen = ref(false);
const tagDialogOpen = ref(false);

const columns = createColumns(task_statuses, tags);

const statusTabs = computed(() => [
    { value: 'all', label: 'All' },
    ...task_statuses.map((status: TaskStatus) => ({
        value: String(status.id),
        label: status.title,
    })),
]);

const handleCreate = () => {
    editingTask.value = null;
    dialogOpen.value = true;
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingTask.value = null;
    router.reload();
};

const handleCreateStatus = () => {
    statusDialogOpen.value = true;
};

const handleViewStatuses = () => {
    router.visit('/task-status');
};

const handleCreateTag = () => {
    tagDialogOpen.value = true;
};

const handleViewTags = () => {
    router.visit(TagController.index().url);
};

const handleTagSuccess = () => {
    tagDialogOpen.value = false;
    router.reload({
        only: ['tags'],
    });
};

const handleStatusSuccess = () => {
    statusDialogOpen.value = false;
    router.reload({
        only: ['task_statuses'],
    });
};

const handleDisplayModeChange = (mode: string) => {
    router.post(
        '/user-preferences/display-mode',
        {
            display_mode: mode,
        },
        {
            preserveState: true,
            onSuccess: () => {
                router.reload({
                    only: ['display_mode'],
                });
            },
        },
    );
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(TaskController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Tasks',
        description: 'Manage tasks and track progress across projects',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Tasks',
            },
        ],
    },
});
</script>

<template>
    <Head title="Tasks" />

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Tasks</h4>
                <p class="text-muted-foreground">
                    Manage tasks and track progress across projects.
                </p>
            </div>
            <ButtonGroup>
                <Button type="button" @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    New Task
                </Button>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">
                            <ListFilter class="mr-2 h-4 w-4" />
                            Statuses
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="handleCreateStatus">
                            <Plus class="mr-2 h-4 w-4" />
                            New Status
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleViewStatuses">
                            <ListFilter class="mr-2 h-4 w-4" />
                            View Statuses
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">
                            <Tag class="mr-2 h-4 w-4" />
                            Tags
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="handleCreateTag">
                            <Plus class="mr-2 h-4 w-4" />
                            New Tag
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleViewTags">
                            <Tag class="mr-2 h-4 w-4" />
                            View Tags
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </ButtonGroup>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex gap-2 border-b" v-if="display_mode === 'list'">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.value"
                    @click="
                        updateFilters({
                            status: tab.value === 'all' ? undefined : tab.value,
                            search: filters?.search,
                        })
                    "
                    :class="[
                        '-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                        filters?.status === tab.value
                            ? 'border-primary text-foreground'
                            : 'border-transparent text-muted-foreground hover:text-foreground',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
            <ButtonGroup class="ml-auto">
                <Button
                    :variant="display_mode === 'list' ? 'default' : 'ghost'"
                    size="sm"
                    @click="handleDisplayModeChange('list')"
                    class="h-7 px-2"
                >
                    <List class="h-3.5 w-3.5" />
                </Button>
                <Button
                    :variant="display_mode === 'kanban' ? 'default' : 'ghost'"
                    size="sm"
                    @click="handleDisplayModeChange('kanban')"
                    class="h-7 px-2"
                >
                    <Kanban class="h-3.5 w-3.5" />
                </Button>
            </ButtonGroup>
        </div>

        <DataTable
            v-if="display_mode === 'list'"
            :columns="columns"
            :data="tasks"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <KanbanView v-else :tasks="tasks" :task_statuses="task_statuses" />

        <TaskFormDialog
            v-model:open="dialogOpen"
            :task_statuses="task_statuses"
            :tags="tags"
            :task="editingTask"
            @success="handleSuccess"
        />

        <TaskStatusFormDialog
            v-model:open="statusDialogOpen"
            :status="null"
            @success="handleStatusSuccess"
        />

        <TagFormDialog
            v-model:open="tagDialogOpen"
            @success="handleTagSuccess"
        />
    </div>
</template>
