<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import TaskStatusController from '@/actions/App/Http/Controllers/TaskStatusController';
import { Button } from '@/components/ui/button';
import { confirm } from '@/composables/useConfirmation';
import type { TaskStatus } from '@/types/models/task_status';

interface Props {
    selectedRows: TaskStatus[];
}

const props = defineProps<Props>();

const hasSelection = computed(() => props.selectedRows.length > 0);
const selectionText = computed(() => {
    const count = props.selectedRows.length;

    return count === 1 ? '1 item selected' : `${count} items selected`;
});

const handleBulkDelete = async () => {
    if (!hasSelection.value) {
        return;
    }

    const confirmed = await confirm({
        title:
            props.selectedRows.length === 1
                ? 'Delete Status'
                : `Delete ${props.selectedRows.length} Statuses`,
        description:
            props.selectedRows.length === 1
                ? 'Are you sure you want to delete this task status? This action cannot be undone.'
                : `Are you sure you want to delete ${props.selectedRows.length} task statuses? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        try {
            const ids = props.selectedRows.map((status) => status.id);
            await router.post(TaskStatusController.bulkDelete().url, { ids });
        } catch (error) {
            console.error('Error during bulk delete:', error);
        }
    }
};
</script>

<template>
    <div
        v-if="hasSelection"
        class="flex items-center justify-between border-b bg-muted/50 p-3"
    >
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-muted-foreground">{{
                selectionText
            }}</span>
        </div>
        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="sm"
                @click="handleBulkDelete"
                class="gap-1"
            >
                <Trash2 class="h-4 w-4" />
                Delete
            </Button>
        </div>
    </div>
</template>
