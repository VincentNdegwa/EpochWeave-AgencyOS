<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import LeadSourceController from '@/actions/App/Http/Controllers/LeadSourceController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { LeadSource } from '@/types/models/lead_source';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import LeadSourceFormDialog from './dialogs/LeadSourceFormDialog.vue';

defineProps<{
    lead_sources: LeadSource[];
    filters: {
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingSource = ref<LeadSource | null>(null);

const columns = createColumns(
    (source) => {
        editingSource.value = source;
        dialogOpen.value = true;
    },
    (source) => {
        router.delete(LeadSourceController.destroy(source.id).url);
    },
);

const handleCreate = () => {
    editingSource.value = null;
    dialogOpen.value = true;
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingSource.value = null;
    router.reload();
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(LeadSourceController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Lead Sources',
        description: 'Manage lead sources for tracking account origin.',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Lead Sources',
            },
        ],
    },
});
</script>

<template>
    <Head title="Lead Sources" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Lead Sources</h4>
                <p class="text-muted-foreground">
                    Manage lead sources for tracking account origin.
                </p>
            </div>
            <Button type="button" @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Lead Source
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="lead_sources"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <LeadSourceFormDialog
            v-model:open="dialogOpen"
            :source="editingSource"
            @success="handleSuccess"
        />
    </div>
</template>
