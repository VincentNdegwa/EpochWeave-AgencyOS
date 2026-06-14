<script setup lang="ts">
import { Tags } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { ProposalStatusModel } from '@/types/models/proposal';

const props = defineProps<{
    columns: ColumnDef<ProposalStatusModel>[];
    data: ProposalStatusModel[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    { key: 'search', type: 'search' as const, placeholder: 'Search statuses...' },
];
</script>

<template>
    <DataTable
        :columns="columns"
        :data="data"
        :filters="filters"
        :on-filter="onFilter"
        :filter-configs="filterConfigs"
        :empty-icon="Tags as Component"
        empty-title="No statuses found"
        empty-description="Try adjusting your search or filters."
        item-name="status"
        item-name-plural="statuses"
    />
</template>
