<script setup lang="ts">
import { Package } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Product } from '@/types/models/product';
import BulkActionsToolbar from './bulk-actions-toolbar.vue';

const props = defineProps<{
    columns: ColumnDef<Product>[];
    data: Product[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    { key: 'search', type: 'search' as const, placeholder: 'Search products...' },
];
</script>

<template>
    <DataTable
        :columns="columns"
        :data="data"
        :filters="filters"
        :on-filter="onFilter"
        :filter-configs="filterConfigs"
        :empty-icon="Package as Component"
        empty-title="No products found"
        empty-description="Try adjusting your search or filters."
        item-name="product"
        item-name-plural="products"
        :bulk-actions="BulkActionsToolbar as Component"
    />
</template>
