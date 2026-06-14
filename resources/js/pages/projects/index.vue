<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref, computed } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { Project } from '@/types/models/project';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import ProjectFormDialog from './dialogs/ProjectFormDialog.vue';

const { projects, project_statuses, filters } = defineProps<{
    projects: Project[];
    project_statuses: any[];
    filters?: {
        status?: string;
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingProject = ref<Project | null>(null);

const columns = createColumns(
    (project) => {
        editingProject.value = project;
        dialogOpen.value = true;
    },
    (project) => {
        router.delete(ProjectController.destroy(project.id).url);
    },
);

const statusTabs = computed(() => [
    { value: 'all', label: 'All' },
    ...project_statuses.map((status: any) => ({
        value: String(status.value),
        label: status.label,
    })),
]);

const handleCreate = () => {
    editingProject.value = null;
    dialogOpen.value = true;
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingProject.value = null;
    router.reload();
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(ProjectController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Projects',
        description: 'Manage your projects and track progress',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Projects',
            },
        ],
    },
});
</script>

<template>
    <Head title="Projects" />

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Projects</h4>
                <p class="text-muted-foreground">
                    Manage your projects and track their status and progress.
                </p>
            </div>
            <div class="flex gap-2">
                <Button type="button" @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    New Project
                </Button>
            </div>
        </div>

        <div class="flex gap-2 border-b">
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

        <DataTable
            :columns="columns"
            :data="projects"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <ProjectFormDialog
            v-model:open="dialogOpen"
            :project="editingProject"
            @success="handleSuccess"
        />
    </div>
</template>
