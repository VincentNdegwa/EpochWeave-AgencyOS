<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import TaskStatusController from '@/actions/App/Http/Controllers/TaskStatusController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { TaskStatus } from '@/types/models/task_status';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import TaskStatusFormDialog from './dialogs/TaskStatusFormDialog.vue';

defineProps<{
    task_statuses: TaskStatus[];
    filters?: {
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingStatus = ref<TaskStatus | null>(null);

const columns = createColumns(
    (status) => {
        editingStatus.value = status;
        dialogOpen.value = true;
    },
    (status) => {
        router.delete(TaskStatusController.destroy(status.id).url);
    },
);

const handleCreate = () => {
    editingStatus.value = null;
    dialogOpen.value = true;
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingStatus.value = null;
    router.reload();
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(TaskStatusController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Task Statuses',
        description: 'Manage task pipeline statuses',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Task Statuses',
            },
        ],
    },
});
</script>

<template>
    <Head title="Task Statuses" />

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Task Statuses</h4>
                <p class="text-muted-foreground">
                    Manage pipeline statuses for tasks across projects.
                </p>
            </div>
            <Button type="button" @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Status
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="task_statuses"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <TaskStatusFormDialog
            v-model:open="dialogOpen"
            :status="editingStatus"
            @success="handleSuccess"
        />
    </div>
</template>
