<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ProductUnitController from '@/actions/App/Http/Controllers/ProductUnitController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { ProductUnit } from '@/types/models/product';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import ProductUnitFormDialog from './dialogs/ProductUnitFormDialog.vue';

defineProps<{
    product_units: ProductUnit[];
}>();

const dialogOpen = ref(false);
const editingUnit = ref<ProductUnit | null>(null);

const columns = createColumns(
    (unit) => {
        editingUnit.value = unit;
        dialogOpen.value = true;
    },
    (unit) => {
        router.delete(ProductUnitController.destroy(unit.id).url);
    },
);

const handleCreate = () => {
    editingUnit.value = null;
    dialogOpen.value = true;
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingUnit.value = null;
    router.reload();
};

defineOptions({
    layout: {
        title: 'Product Units',
        description: 'Manage product measurement units',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Product Units',
            },
        ],
    },
});
</script>

<template>
    <Head title="Product Units" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Product Units</h1>
                <p class="text-muted-foreground">
                    Manage measurement units for your products (e.g., hours,
                    months, flat fee).
                </p>
            </div>
            <Button @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Unit
            </Button>
        </div>

        <!-- Data Table -->
        <DataTable :columns="columns" :data="product_units" />

        <!-- Dialog -->
        <ProductUnitFormDialog
            v-model:open="dialogOpen"
            :unit="editingUnit"
            @success="handleSuccess"
        />
    </div>
</template>
