<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import IndustryController from '@/actions/App/Http/Controllers/IndustryController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { Industry } from '@/types/models/industry';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import IndustryFormDialog from './dialogs/IndustryFormDialog.vue';

defineProps<{
    industries: Industry[];
    filters: {
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingIndustry = ref<Industry | null>(null);

const columns = createColumns(
    (industry) => {
        editingIndustry.value = industry;
        dialogOpen.value = true;
    },
    (industry) => {
        router.delete(IndustryController.destroy(industry.id).url);
    },
);

const handleCreate = () => {
    editingIndustry.value = null;
    dialogOpen.value = true;
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingIndustry.value = null;
    router.reload();
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(IndustryController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Industries',
        description: 'Manage the industries available for your accounts.',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Industries',
            },
        ],
    },
});
</script>

<template>
    <Head title="Industries" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Industries</h4>
                <p class="text-muted-foreground">
                    Manage the industries available for your accounts.
                </p>
            </div>
            <Button type="button" @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Industry
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="industries"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <IndustryFormDialog
            v-model:open="dialogOpen"
            :industry="editingIndustry"
            @success="handleSuccess"
        />
    </div>
</template>
