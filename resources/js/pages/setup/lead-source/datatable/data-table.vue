<script setup lang="ts">
import { Target } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { LeadSource } from '@/types/models/lead_source';

defineProps<{
    columns: ColumnDef<LeadSource>[];
    data: LeadSource[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    {
        key: 'search',
        type: 'search' as const,
        placeholder: 'Search lead sources...',
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
        :empty-icon="Target as Component"
        empty-title="No lead sources found"
        empty-description="Try adjusting your search or filters."
        item-name="lead source"
        item-name-plural="lead sources"
    />
</template>
