<script setup lang="ts">
import { FolderKanban } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { Component } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Project } from '@/types/models/project';
import BulkActionsToolbar from './bulk-actions-toolbar.vue';

const props = defineProps<{
    columns: ColumnDef<Project>[];
    data: Project[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
}>();

const filterConfigs = [
    { key: 'search', type: 'search' as const, placeholder: 'Search projects...' },
];
</script>

<template>
    <DataTable
        :columns="columns"
        :data="data"
        :filters="filters"
        :on-filter="onFilter"
        :filter-configs="filterConfigs"
        :empty-icon="FolderKanban as Component"
        empty-title="No projects found"
        empty-description="Try adjusting your search or filters."
        item-name="project"
        item-name-plural="projects"
        :bulk-actions="BulkActionsToolbar as Component"
    />
</template>
