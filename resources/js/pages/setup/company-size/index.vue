<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import CompanySizeController from '@/actions/App/Http/Controllers/CompanySizeController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { CompanySize } from '@/types/models/company_size';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import CompanySizeFormDialog from './dialogs/CompanySizeFormDialog.vue';

defineProps<{
    company_sizes: CompanySize[];
    filters: {
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingSize = ref<CompanySize | null>(null);

const columns = createColumns(
    (size) => {
        editingSize.value = size;
        dialogOpen.value = true;
    },
    (size) => {
        router.delete(CompanySizeController.destroy(size.id).url);
    },
);

const handleCreate = () => {
    editingSize.value = null;
    dialogOpen.value = true;
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingSize.value = null;
    router.reload();
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(CompanySizeController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Company Sizes',
        description: 'Manage company size ranges for your accounts.',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Company Sizes',
            },
        ],
    },
});
</script>

<template>
    <Head title="Company Sizes" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Company Sizes</h4>
                <p class="text-muted-foreground">
                    Manage company size ranges for your accounts.
                </p>
            </div>
            <Button type="button" @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Company Size
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="company_sizes"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <CompanySizeFormDialog
            v-model:open="dialogOpen"
            :size="editingSize"
            @success="handleSuccess"
        />
    </div>
</template>
