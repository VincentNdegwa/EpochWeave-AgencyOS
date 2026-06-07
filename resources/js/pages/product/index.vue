<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Package } from '@lucide/vue';
import { ref } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { dashboard } from '@/routes';
import { index as productUnitIndex } from '@/routes/product-units';
import type { Product, ProductUnit } from '@/types/models/product';
import ProductUnitFormDialog from '../product-unit/dialogs/ProductUnitFormDialog.vue';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import ProductFormDialog from './dialogs/ProductFormDialog.vue';

defineProps<{
    products: Product[];
    units: ProductUnit[];
}>();

const dialogOpen = ref(false);
const editingProduct = ref<Product | null>(null);
const unitDialogOpen = ref(false);

const columns = createColumns(
    (product) => {
        editingProduct.value = product;
        dialogOpen.value = true;
    },
    (product) => {
        router.delete(ProductController.destroy(product.id).url);
    },
);

const handleCreate = () => {
    editingProduct.value = null;
    dialogOpen.value = true;
};

const handleCreateUnit = () => {
    unitDialogOpen.value = true;
};

const handleViewUnits = () => {
    router.visit(productUnitIndex().url);
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingProduct.value = null;
    router.reload();
};

const handleUnitSuccess = () => {
    unitDialogOpen.value = false;
    router.reload({
        only: ['units'],
        preserveScroll: true,
        preserveState: true,
    });
};

defineOptions({
    layout: {
        title: 'Products',
        description: 'Manage your products and services',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Products',
            },
        ],
    },
});
</script>

<template>
    <Head title="Products" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Products</h1>
                <p class="text-muted-foreground">
                    Manage your products and services with pricing and billing
                    information.
                </p>
            </div>
            <div class="flex gap-2">
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    New Product
                </Button>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">
                            <Package class="mr-2 h-4 w-4" />
                            Units
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="handleCreateUnit">
                            <Plus class="mr-2 h-4 w-4" />
                            New Unit
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleViewUnits">
                            <Package class="mr-2 h-4 w-4" />
                            View Units
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- Data Table -->
        <DataTable :columns="columns" :data="products" />

        <!-- Dialog -->
        <ProductFormDialog
            v-model:open="dialogOpen"
            :product="editingProduct"
            :units="units"
            @create-unit="handleCreateUnit"
            @success="handleSuccess"
        />

        <!-- Unit Dialog -->
        <ProductUnitFormDialog
            v-model:open="unitDialogOpen"
            @success="handleUnitSuccess"
        />
    </div>
</template>
