<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import CustomFieldController from '@/actions/App/Http/Controllers/CustomFieldController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type {
    CustomFieldDefinition,
    CustomFieldGroup,
} from '@/types/models/custom_field';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import DefinitionFormDialog from './dialogs/DefinitionFormDialog.vue';
import DefinitionsListDialog from './dialogs/DefinitionsListDialog.vue';
import GroupFormDialog from './dialogs/GroupFormDialog.vue';

defineProps<{
    groups: CustomFieldGroup[];
    filters: {
        search?: string;
    };
}>();

const groupDialogOpen = ref(false);
const definitionDialogOpen = ref(false);
const definitionsListDialogOpen = ref(false);
const editingGroup = ref<CustomFieldGroup | null>(null);
const managingGroup = ref<CustomFieldGroup | null>(null);
const editingDefinition = ref<CustomFieldDefinition | null>(null);
const activeGroupId = ref<number>(0);

const columns = createColumns(
    (group) => {
        editingGroup.value = group;
        groupDialogOpen.value = true;
    },
    (group) => {
        router.delete(CustomFieldController.destroyGroup(group.id).url);
    },
    (group) => {
        activeGroupId.value = group.id;
        editingDefinition.value = null;
        definitionDialogOpen.value = true;
    },
    (group) => {
        managingGroup.value = group;
        definitionsListDialogOpen.value = true;
    },
);

const handleCreateGroup = () => {
    editingGroup.value = null;
    groupDialogOpen.value = true;
};

const handleSuccess = () => {
    groupDialogOpen.value = false;
    definitionDialogOpen.value = false;
    definitionsListDialogOpen.value = false;
    editingGroup.value = null;
    managingGroup.value = null;
    editingDefinition.value = null;
    router.reload();
};

const handleEditDefinition = (definition: CustomFieldDefinition) => {
    editingDefinition.value = definition;
    activeGroupId.value = definition.custom_field_group_id;
    definitionsListDialogOpen.value = false;
    definitionDialogOpen.value = true;
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(CustomFieldController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Custom Fields',
        description: 'Manage custom field groups and definitions for your records.',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Custom Fields',
            },
        ],
    },
});
</script>

<template>
    <Head title="Custom Fields" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Custom Fields</h4>
                <p class="text-muted-foreground">
                    Manage custom field groups and definitions for your records.
                </p>
            </div>
            <Button type="button" @click="handleCreateGroup">
                <Plus class="mr-2 h-4 w-4" />
                New Group
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="groups"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <GroupFormDialog
            v-model:open="groupDialogOpen"
            :group="editingGroup"
            @success="handleSuccess"
        />

        <DefinitionFormDialog
            v-model:open="definitionDialogOpen"
            :definition="editingDefinition"
            :group-id="activeGroupId"
            @success="handleSuccess"
        />

        <DefinitionsListDialog
            v-model:open="definitionsListDialogOpen"
            :group="managingGroup"
            @edit="handleEditDefinition"
        />
    </div>
</template>
