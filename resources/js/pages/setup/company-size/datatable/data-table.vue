<script setup lang="ts">
import { Building2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { CompanySize } from '@/types/models/company_size';

defineProps<{
    columns: ColumnDef<CompanySize>[];
    data: CompanySize[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    {
        key: 'search',
        type: 'search' as const,
        placeholder: 'Search company sizes...',
    },
];
</script>

<template>
    <DataTable
        :columns="columns"
        :data="data"
        :filters="filters"
        :on-filter="onFilter"
        :filter-configs="filterConfigs"
        :empty-icon="Building2 as Component"
        empty-title="No company sizes found"
        empty-description="Try adjusting your search or filters."
        item-name="company size"
        item-name-plural="company sizes"
    />
</template>
