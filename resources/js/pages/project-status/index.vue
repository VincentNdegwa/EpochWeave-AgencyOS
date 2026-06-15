<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ProjectStatusController from '@/actions/App/Http/Controllers/ProjectStatusController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { ProjectStatus } from '@/types/models/project_status';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import ProjectStatusFormDialog from './dialogs/ProjectStatusFormDialog.vue';

defineProps<{
    project_statuses: ProjectStatus[];
    filters: {
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingStatus = ref<ProjectStatus | null>(null);

const columns = createColumns(
    (status) => {
        editingStatus.value = status;
        dialogOpen.value = true;
    },
    (status) => {
        router.delete(ProjectStatusController.destroy(status.id).url);
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
    router.get(ProjectStatusController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Project Statuses',
        description: 'Manage project status labels and colors',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Project Statuses',
            },
        ],
    },
});
</script>

<template>
    <Head title="Project Statuses" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Project Statuses</h4>
                <p class="text-muted-foreground">
                    Manage status labels and colors for your projects workflow.
                </p>
            </div>
            <Button type="button" @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Status
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="project_statuses"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <ProjectStatusFormDialog
            v-model:open="dialogOpen"
            :status="editingStatus"
            @success="handleSuccess"
        />
    </div>
</template>
