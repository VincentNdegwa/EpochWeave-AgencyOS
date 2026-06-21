<script setup lang="ts">
import { Building2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Account } from '@/types/models/account';
import BulkActionsToolbar from './bulk-actions-toolbar.vue';

defineProps<{
    columns: ColumnDef<Account>[];
    data: Account[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    {
        key: 'search',
        type: 'search' as const,
        placeholder: 'Search accounts...',
    },
    { key: 'date_from', type: 'date' as const, placeholder: 'From' },
    { key: 'date_to', type: 'date' as const, placeholder: 'To' },
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
        empty-title="No accounts found"
        empty-description="Try adjusting your search or filters."
        item-name="account"
        item-name-plural="accounts"
        :bulk-actions="BulkActionsToolbar as Component"
    />
</template>
