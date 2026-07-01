<script setup lang="ts">
import { List } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { CustomFieldGroup } from '@/types/models/custom_field';

defineProps<{
    columns: ColumnDef<CustomFieldGroup>[];
    data: CustomFieldGroup[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    {
        key: 'search',
        type: 'search' as const,
        placeholder: 'Search custom field groups...',
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
        :empty-icon="List as Component"
        empty-title="No custom field groups found"
        empty-description="Try adjusting your search or filters."
        item-name="group"
        item-name-plural="groups"
    />
</template>
