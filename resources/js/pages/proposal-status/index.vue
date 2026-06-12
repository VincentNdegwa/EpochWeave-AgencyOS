<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ProposalStatusController from '@/actions/App/Http/Controllers/ProposalStatusController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { ProposalStatusModel } from '@/types/models/proposal';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import ProposalStatusFormDialog from './dialogs/ProposalStatusFormDialog.vue';

defineProps<{
    proposal_statuses: ProposalStatusModel[];
    filters: {
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingStatus = ref<ProposalStatusModel | null>(null);

const columns = createColumns(
    (status) => {
        editingStatus.value = status;
        dialogOpen.value = true;
    },
    (status) => {
        router.delete(ProposalStatusController.destroy(status.id).url);
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
    router.get(ProposalStatusController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Proposal Statuses',
        description: 'Manage proposal status labels and colors',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Proposal Statuses',
            },
        ],
    },
});
</script>

<template>
    <Head title="Proposal Statuses" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Proposal Statuses</h4>
                <p class="text-muted-foreground">
                    Manage status labels and colors for your proposals workflow.
                </p>
            </div>
            <Button type="button" @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Status
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="proposal_statuses"
            :search-value="filters?.search"
            :on-search-update="(value: string | number) => updateFilters({ search: String(value) })"
        />

        <ProposalStatusFormDialog
            v-model:open="dialogOpen"
            :status="editingStatus"
            @success="handleSuccess"
        />
    </div>
</template>
