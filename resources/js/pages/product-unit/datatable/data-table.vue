<script setup lang="ts">
import { Boxes } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { ProductUnit } from '@/types/models/product';

defineProps<{
    columns: ColumnDef<ProductUnit>[];
    data: ProductUnit[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    { key: 'search', type: 'search' as const, placeholder: 'Search units...' },
];
</script>

<template>
    <DataTable
        :columns="columns"
        :data="data"
        :filters="filters"
        :on-filter="onFilter"
        :filter-configs="filterConfigs"
        :empty-icon="Boxes as Component"
        empty-title="No units found"
        empty-description="Try adjusting your search or filters."
        item-name="unit"
        item-name-plural="units"
    />
</template>
