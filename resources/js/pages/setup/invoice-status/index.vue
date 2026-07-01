<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import InvoiceStatusController from '@/actions/App/Http/Controllers/InvoiceStatusController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { InvoiceStatus } from '@/types/models/invoice_status';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import InvoiceStatusFormDialog from './dialogs/InvoiceStatusFormDialog.vue';

defineProps<{
    invoice_statuses: InvoiceStatus[];
    filters: {
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingStatus = ref<InvoiceStatus | null>(null);

const columns = createColumns(
    (status) => {
        editingStatus.value = status;
        dialogOpen.value = true;
    },
    (status) => {
        router.delete(InvoiceStatusController.destroy(status.id).url);
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
    router.get(InvoiceStatusController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Invoice Statuses',
        description: 'Manage invoice status labels and colors',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Invoice Statuses',
            },
        ],
    },
});
</script>

<template>
    <Head title="Invoice Statuses" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Invoice Statuses</h4>
                <p class="text-muted-foreground">
                    Manage status labels and colors for your invoices workflow.
                </p>
            </div>
            <Button type="button" @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Status
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="invoice_statuses"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <InvoiceStatusFormDialog
            v-model:open="dialogOpen"
            :status="editingStatus"
            @success="handleSuccess"
        />
    </div>
</template>
