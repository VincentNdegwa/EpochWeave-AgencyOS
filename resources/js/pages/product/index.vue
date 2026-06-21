<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Package } from '@lucide/vue';
import { ref } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { StatBar } from '@/components/ui/stat-bar';
import { dashboard } from '@/routes';
import { index as productUnitIndex } from '@/routes/product-units';
import type { Product, ProductUnit } from '@/types/models/product';
import ProductUnitFormDialog from '../product-unit/dialogs/ProductUnitFormDialog.vue';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import ProductFormDialog from './dialogs/ProductFormDialog.vue';

const props = defineProps<{
    products: Product[];
    units: ProductUnit[];
    stats: {
        total: { value: number; change?: { value: number; label: string } };
        active: { value: number; change?: { value: number; label: string } };
        inactive: { value: number; change?: { value: number; label: string } };
    };
    filters: {
        status: string;
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingProduct = ref<Product | null>(null);
const unitDialogOpen = ref(false);

const columns = createColumns(props.units);

const statusTabs = [
    { value: 'all', label: 'All' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

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

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(ProductController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

const handleUnitSuccess = () => {
    unitDialogOpen.value = false;
    router.reload({
        only: ['units'],
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

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Products</h4>
                <p class="text-muted-foreground">
                    Manage your products and services with pricing and billing
                    information.
                </p>
            </div>
            <ButtonGroup>
                <Button type="button" @click="handleCreate">
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
            </ButtonGroup>
        </div>

        <StatBar
            :items="[
                {
                    label: 'Total Products',
                    value: stats.total.value,
                    change: stats.total.change,
                },
                {
                    label: 'Active',
                    value: stats.active.value,
                    change: stats.active.change,
                },
                {
                    label: 'Inactive',
                    value: stats.inactive.value,
                    change: stats.inactive.change,
                },
            ]"
            :columns="3"
        />

        <div class="flex gap-2 border-b">
            <button
                v-for="tab in statusTabs"
                :key="tab.value"
                @click="
                    updateFilters({
                        status: tab.value === 'all' ? undefined : tab.value,
                        search: filters.search,
                    })
                "
                :class="[
                    '-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                    filters.status === tab.value
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground',
                ]"
            >
                {{ tab.label }}
            </button>
        </div>

        <DataTable
            :columns="columns"
            :data="products"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <ProductFormDialog
            v-model:open="dialogOpen"
            :product="editingProduct"
            :units="units"
            @create-unit="handleCreateUnit"
            @success="handleSuccess"
        />

        <ProductUnitFormDialog
            v-model:open="unitDialogOpen"
            @success="handleUnitSuccess"
        />
    </div>
</template>
