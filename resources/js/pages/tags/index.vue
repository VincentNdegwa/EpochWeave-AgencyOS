<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Tag } from '@lucide/vue';
import { ref } from 'vue';
import TagController from '@/actions/App/Http/Controllers/TagController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { Tag as TagType } from '@/types/models/tag';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import TagFormDialog from './dialogs/TagFormDialog.vue';

defineProps<{
    tags: TagType[];
    filters?: {
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingTag = ref<TagType | null>(null);

const columns = createColumns(
    (tag) => {
        editingTag.value = tag;
        dialogOpen.value = true;
    },
    (tag) => {
        router.delete(TagController.destroy(tag.id).url);
    },
);

const handleCreate = () => {
    editingTag.value = null;
    dialogOpen.value = true;
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingTag.value = null;
    router.reload();
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(TagController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Tags',
        description: 'Manage tags for tasks',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Tags',
            },
        ],
    },
});
</script>

<template>
    <Head title="Tags" />

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Tags</h4>
                <p class="text-muted-foreground">
                    Manage tags for organizing tasks.
                </p>
            </div>
            <Button type="button" @click="handleCreate">
                <Plus class="mr-2 h-4 w-4" />
                New Tag
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="tags"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <TagFormDialog
            v-model:open="dialogOpen"
            :tag="editingTag"
            @success="handleSuccess"
        />
    </div>
</template>
