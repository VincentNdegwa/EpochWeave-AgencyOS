<script setup lang="ts">
import { FileStack } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Proposal } from '@/types/models/proposal';
import BulkActionsToolbar from './bulk-actions-toolbar.vue';

defineProps<{
    columns: ColumnDef<Proposal>[];
    data: Proposal[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    {
        key: 'search',
        type: 'search' as const,
        placeholder: 'Search proposals...',
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
        :empty-icon="FileStack as Component"
        empty-title="No proposals found"
        empty-description="Try adjusting your search or filters."
        item-name="proposal"
        item-name-plural="proposals"
        :bulk-actions="BulkActionsToolbar as Component"
    />
</template>
