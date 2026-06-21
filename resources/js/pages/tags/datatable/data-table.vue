<script setup lang="ts">
import { Tag } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Tag as TagType } from '@/types/models/tag';
import BulkActionsToolbar from './bulk-actions-toolbar.vue';

defineProps<{
    columns: ColumnDef<TagType>[];
    data: TagType[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    { key: 'search', type: 'search' as const, placeholder: 'Search tags...' },
];
</script>

<template>
    <DataTable
        :columns="columns"
        :data="data"
        :filters="filters"
        :on-filter="onFilter"
        :filter-configs="filterConfigs"
        :empty-icon="Tag as Component"
        empty-title="No tags found"
        empty-description="Try adjusting your search or filters."
        item-name="tag"
        item-name-plural="tags"
        :bulk-actions="BulkActionsToolbar as Component"
    />
</template>
