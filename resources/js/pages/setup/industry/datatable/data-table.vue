<script setup lang="ts">
import { Factory } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Industry } from '@/types/models/industry';

defineProps<{
    columns: ColumnDef<Industry>[];
    data: Industry[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    {
        key: 'search',
        type: 'search' as const,
        placeholder: 'Search industries...',
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
        :empty-icon="Factory as Component"
        empty-title="No industries found"
        empty-description="Try adjusting your search or filters."
        item-name="industry"
        item-name-plural="industries"
    />
</template>
