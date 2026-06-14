<script setup lang="ts">
import { ListTodo } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Task } from '@/types/models/task';
import BulkActionsToolbar from './bulk-actions-toolbar.vue';

const props = defineProps<{
    columns: ColumnDef<Task>[];
    data: Task[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    { key: 'search', type: 'search' as const, placeholder: 'Search tasks...' },
];
</script>

<template>
    <DataTable
        :columns="columns"
        :data="data"
        :filters="filters"
        :on-filter="onFilter"
        :filter-configs="filterConfigs"
        :empty-icon="ListTodo as Component"
        empty-title="No tasks found"
        empty-description="Try adjusting your search or filters."
        item-name="task"
        item-name-plural="tasks"
        :bulk-actions="BulkActionsToolbar as Component"
    />
</template>
