<script setup lang="ts">
import { FileText } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Invoice } from '@/types/models/invoice';
import BulkActionsToolbar from './bulk-actions-toolbar.vue';

defineProps<{
    columns: ColumnDef<Invoice>[];
    data: Invoice[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    {
        key: 'search',
        type: 'search' as const,
        placeholder: 'Search invoices...',
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
        :empty-icon="FileText as Component"
        empty-title="No invoices found"
        empty-description="Try adjusting your search or filters."
        item-name="invoice"
        item-name-plural="invoices"
        :bulk-actions="BulkActionsToolbar as Component"
    />
</template>
